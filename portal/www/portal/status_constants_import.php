var GENI_READY = "<?php echo STATUS_INDEX::GENI_READY ?>";
var GENI_NO_RESOURCES = "<?php echo STATUS_INDEX::GENI_NO_RESOURCES ?>";
var GENI_BOOTING = "<?php echo STATUS_INDEX::GENI_BOOTING ?>";
var GENI_BUSY = "<?php echo STATUS_INDEX::GENI_BUSY ?>";

var GENI_READY_STR = "<?php echo STATUS_MSG::GENI_READY ?>";
var GENI_NO_RESOURCES_STR = "<?php echo STATUS_MSG::GENI_NO_RESOURCES ?>";
var GENI_BOOTING_STR = "<?php echo STATUS_MSG::GENI_BOOTING ?>";
var GENI_BUSY_STR = "<?php echo STATUS_MSG::GENI_BUSY ?>";

var GENI_READY_CLASS = "<?php echo STATUS_CLASS::GENI_READY ?>";
var GENI_NO_RESOURCES_CLASS = "<?php echo STATUS_CLASS::GENI_NO_RESOURCES ?>";
var GENI_BOOTING_CLASS = "<?php echo STATUS_CLASS::GENI_BOOTING ?>";
var GENI_BUSY_CLASS = "<?php echo STATUS_CLASS::GENI_BUSY ?>";

var GENI_MESSAGES;
GENI_MESSAGES = Array();
GENI_MESSAGES[ GENI_READY ] = GENI_READY_STR;
GENI_MESSAGES[ GENI_NO_RESOURCES ] = GENI_NO_RESOURCES_STR;
GENI_MESSAGES[ GENI_BOOTING ] = GENI_BOOTING_STR;
GENI_MESSAGES[ GENI_BUSY ] = GENI_BUSY_STR;

var GENI_CLASSES;
GENI_CLASSES = Array();
GENI_CLASSES[ GENI_READY ] = GENI_READY_CLASS;
GENI_CLASSES[ GENI_NO_RESOURCES ] = GENI_NO_RESOURCES_CLASS;
GENI_CLASSES[ GENI_BOOTING ] = GENI_BOOTING_CLASS;
GENI_CLASSES[ GENI_BUSY ] = GENI_BUSY_CLASS;

var slice= "<?php echo $slice_id ?>";
var am_id= "<?php echo $am_id ?>";
