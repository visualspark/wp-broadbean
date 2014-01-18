<?php

/******************************************
	Convert an arbitrary sized decimal (in float format) 
	to and array of 16-bit integers
******************************************/
Function float2largearray($n) {
  $result = array();
  while ($n > 0) {
    array_push($result, ($n & 0xffff));
    list($n, $dummy) = explode('.', sprintf("%F", $n/65536.0));
    # note we don't want to use "%0.F" as it will get rounded which is bad.
  }
  return $result;
}

/******************************************
	Convert our largearray format back to 
	an arbitrary sized whole number float
******************************************/
Function largearray2float($a) {
  $factor = 1.0;
  $result = 0.0;
  foreach ($a as $element) {
    $result += ($factor * $element);
    $factor = $factor << 16;
  }
  list($result, $dummy) = explode('.', sprintf("%F", $result));
  return $result;
}

/******************************************
	Perform a bitwise AND operation of $a and $b
	We only need to operate on the minimum number of elements because any extra elements
	in any array would be negated by the AND with the implied zeros in the smaller array
******************************************/
Function largearray_and($a, $b) {
  $indexes = min(count($a), count($b));
  $c = array();
  for ($i=0; $i<$indexes; $i++) {
    array_push($c, $a[$i] & $b[$i]);
  }
  return $c;
}

Function largearray_or($a, $b) {
  $indexes = max(count($a), count($b));
  $c = array();
  for ($i=0; $i<$indexes; $i++) {
    if (!isset($a[$i])) $a[$i] = 0;
    if (!isset($b[$i])) $b[$i] = 0;
    array_push($c, $a[$i] | $b[$i]);
  }
  return $c;
}

Function float_and($a, $b) {
  return
    largearray2float(
      largearray_and( float2largearray($a), float2largearray($b) )
    );
}
  
Function float_or($a, $b) {
  return
    largearray2float(
      largearray_or( float2largearray($a), float2largearray($b) )
    );
}
  

?>