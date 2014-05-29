<?php
//----------------------------------------------------------------------
// Copyright (c) 2014 Raytheon BBN Technologies
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
require_once("header.php");
show_header('GENI Portal: Slices', $TAB_SLICES);
include("tool-breadcrumbs.php");
include("tool-showmessage.php");
$location = $_GET['loc'];
include("tool-lookupids.php");

print "<p class='warn'>";
if (isset($am_id) && $am_id) {
	print "This action will query ".count($ams)." aggregates and may take several minutes.";
  print '<br>Are you sure that you want to query all '.count($ams).' aggregates?';
  for ($i = 0; $i < count($ams); $i++) {
    $location = $location."&am_id[]=".$am_ids[$i];
  }
}
else {
	print 'This action will query all aggregates and may take several minutes.';
	print '<br>Are you sure that you want to query all aggregates?';
}

//print "<button onClick=\"window.location=$location\"><b>YES</b></button><br/>\n";
print "<form method='POST' action=\"$location\">";
print '<input type="submit" value="YES" style="width:60px;height:30px"/>';
print '<input type="button" value="CANCEL" onclick="history.back(-1)" style="width:60px;height:30px"/>';
print "</form></p>";

include("footer.php");
?>