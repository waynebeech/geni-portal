<?php
//----------------------------------------------------------------------
// Copyright (c) 2012-2016 Raytheon BBN Technologies
//
// Permission is hereby granted, free of charge, to any person obtaining
// a copy of this software and/or hardware specification (the "Work") to
// deal in the Work without restriction, including without limitation the
// rights to use, copy, modify, merge, publish, distribute, sublicense,
// and/or sell copies of the Work, and to permit persons to whom the Work
// is furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be
// included in all copies or substantial portions of the Work.
//
// THE WORK IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
// OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
// MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
// NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
// HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
// WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE WORK OR THE USE OR OTHER DEALINGS
// IN THE WORK.
//----------------------------------------------------------------------

require_once("user.php");
require_once("header.php");
require_once('util.php');
require_once('pa_constants.php');
require_once('sa_constants.php');
require_once('pa_client.php');
require_once('sa_client.php');
require_once('sr_constants.php');
require_once('sr_client.php');


// Check the selections from the edit-project-member table pull-downs
// Validate that there must always be exaclty one lead.
// Create the various calls to the PA:
//    change_lead
//    add_project_member
//    remove_project_member
//    change_member_role
// Return success or failure depending on results from these calls   


$user = geni_loadUser();
if (!isset($user) || is_null($user) || ! $user->isActive()) {
  relative_redirect('home.php');
}

if (! isset($sa_url)) {
  $sa_url = get_first_service_of_type(SR_SERVICE_TYPE::SLICE_AUTHORITY);
}

// Add project lead as lead of slice
// Change to lead if already a member
function add_project_lead_as_slice_lead($slice_id, $project_members_by_role, $slice_members)
{
  global $sa_url;
  global $user;

  $project_lead = null;
  //  error_log("PMBRS = " . print_r($project_members_by_role, true));
  foreach($project_members_by_role as $pm => $pm_role) {
    if($pm_role == CS_ATTRIBUTE_TYPE::LEAD) {
      $project_lead = $pm;
      break;
    }
  }

  $project_lead_is_slice_member = false;
  foreach($slice_members as $slice_member) {
    $slice_member_id = $slice_member[SA_SLICE_MEMBER_TABLE_FIELDNAME::MEMBER_ID];
    if ($slice_member_id == $project_lead) {
      $project_lead_is_slice_member = true;
      break;
    }
  }
  //  error_log("Lead is in project: " .  $project_lead_is_slice_member);

  // If project lead is already a member, make into lead
  // Otherwise add as lead
  if($project_lead_is_slice_member) {
    error_log("Changing " . $project_lead . " to lead of slice " . $slice_id);
    change_slice_member_role($sa_url, $user, $slice_id, $project_lead, CS_ATTRIBUTE_TYPE::LEAD);
  } else {
    error_log("Adding " . $project_lead . " to lead of slice " . $slice_id);
    add_slice_member($sa_url, $user, $slice_id, $project_lead, CS_ATTRIBUTE_TYPE::LEAD);
  }

}

// Remove a member from a project
// Find all slices to which the member belongs of this project and remove from them as well
// If the member is the lead of the slice, make the project lead the member of the slice
// When all this is done, remove member from project
function remove_project_member_from_project_and_slices($project_id, $project_member_id, $project_members_by_role)
{
  global $sa_url;
  global $user;

  // Get the project slices and memberships
  $slice_members = get_slice_members_for_project($sa_url, $user, $project_id);
  //  error_log("SM = " . print_r($slice_members, true));
  foreach($slice_members as $slice_member) {
    $removed_lead = false;
    $slice_id = $slice_member[SA_SLICE_MEMBER_TABLE_FIELDNAME::SLICE_ID];
    $slice_member_id = $slice_member[SA_SLICE_MEMBER_TABLE_FIELDNAME::MEMBER_ID];
    $slice_member_role = $slice_member[SA_SLICE_MEMBER_TABLE_FIELDNAME::ROLE];
    if ($slice_member_id == $project_member_id) {
      error_log("   Removing " . $slice_member_id . " from slice " . $slice_id . " " . $slice_member_role);
      if ($slice_member_role == CS_ATTRIBUTE_TYPE::LEAD) {
	$removed_lead = true;
      }
    }

    //      remove_slice_member($sa_url, $user, $slice_id, $slice_member_id);
    if($removed_lead) {
      error_log("Removed slice lead : " . $slice_id . " " . $project_member_id);
      add_project_lead_as_slice_lead($slice_id, $project_members_by_role, $slice_members);
    }
  }

  error_log("Removing " . $project_member_id . " from project " . $project_id);
  remove_project_member($sa_url, $user, $project_id, $project_member_id);
}

// Ensure that the new roles maintains a single project lead
// Additionally, one can't remove one's self as a member of a project
//   (someone has to do it for you).
function validate_project_member_requests($project_members_by_role, $selections)
{
  global $user;

  //  error_log("PMBR = " . print_r($project_members_by_role, True));
  //  error_log("SELS = " . print_r($selections, True));

  $excluding_self = False;
  foreach($selections as $member_id => $sel) {
    if($user->account_id == $member_id && 
       $sel == 0 && 
       array_key_exists($member_id, $project_members_by_role)) {
      $excluding_self = True;
      break;
    }
  }
  if ($excluding_self) {
    return array('success' => False, 'text' => "Cannot remove self from project");
  }

  // Count the number of people that are slated to be lead
  $lead_count = 0;
  foreach($selections as $member_id => $sel) {
    if ($sel == CS_ATTRIBUTE_TYPE::LEAD) { // Changing to or maintaining a lead
      $lead_count += 1;
    }
  }
  // See if there are any current members they are trying to change to lead
  // See if there are any non-members they are trying to add as lead
  // Total number of leads must be exactly 1
  if ($lead_count == 1) {
    $message = '';
    $success = True;
  } else {
    $message = "Number of leads for project must be exactly 1.";
    $success = False;
  }
    
  //  error_log("SUCCESS = $success, TEXT = $message");
  return array('success' => $success, 'text' => $message);
}

function do_modify_project_membership($selections, $project_id, $project_members_by_role)
{
  global $user;
  global $sa_url;

  $members_to_add = array();
  $members_to_change_role = array();
  $members_to_remove = array();

  //  error_log("Selections = " . print_r($selections, true));
  //  error_log("PMBR = " . print_r($project_members_by_role, true));

  foreach($selections as $member_id => $selection_id) {
    $is_member = array_key_exists($member_id, $project_members_by_role);
    if ($is_member) {
      $role = $project_members_by_role[$member_id];
      if($selection_id == 0) {
	// Remove this member from this slice
	$members_to_remove[] = $member_id;
      } else if ($selection_id != $role) {
	// Change the role of this member on this slice
	$members_to_change_role[$member_id] = $selection_id;
      }
    } else {
      if ($selection_id > 0) {
	// Add member to slice
	$members_to_add[$member_id] = $selection_id;
      }
    }
  }

  // Publish changes atomically to SA
  $result = modify_project_membership($sa_url, $user, $project_id, 
				      $members_to_add, 
				      $members_to_change_role, 
				      $members_to_remove);
  return $result;
}

// error_log("REQUEST = " . print_r($_REQUEST, true));
$project_id = $_REQUEST['project_id'];
unset($_REQUEST['project_id']);
$project_members = get_project_members($sa_url, $user, $project_id);
$project_members_by_role = array();
foreach($project_members as $project_member) {
  $project_member_id = $project_member['member_id'];
  $project_member_role = $project_member['role'];
  $project_members_by_role[$project_member_id] = $project_member_role;
}
$selections = $_REQUEST;

$validation_result = validate_project_member_requests($project_members_by_role, $selections);
$success = $validation_result['success'];
if($success) {
  $result = do_modify_project_membership($selections, $project_id, $project_members_by_role);
  if ($result[RESPONSE_ARGUMENT::CODE] == RESPONSE_ERROR::NONE)
    $_SESSION['lastmessage'] = "Project membership successfully changed.";
  else
    $_SESSION['lastmessage'] = 'Error changing project membership : ' .$result[RESPONSE_ARGUMENT::OUTPUT];
} else {
  $result = $validation_result['text'];
  $_SESSION['lasterror'] = $result;
}

relative_redirect("project.php?project_id=".$project_id);

?>

