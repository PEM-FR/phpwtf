<?php


// @wtf_start 
// could not you just return (!!eval($condition))
if ((eval($condition))) {
	return true;
} else {
	return false;
}
// @wtf_stop



// @wtf_start 
// Either remove the condition or refactor the code to allow switching in context
if (false) {
	// something no longer used
} else {
	// the only thing we still do now
}
// @wtf_stop