<?php
/******************************************
 Function to determine if an IP is located 
 in a specific range as specified via several 
 alternative formats.
 
 * Network ranges can be specified as:
 * 1. Wildcard format:     1.2.3.*
 * 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
 * 3. Start-End IP format: 1.2.3.0-1.2.3.255
 
 * Return value BOOLEAN : ip_in_range($ip, $range);
******************************************/

include ("bitwise_float.php");

Function ip2float($ipstring) {
  $num = (float)sprintf("%u",ip2long($ipstring));
  return $num;
}

Function ip6floatA($ipstring) {
  $ip6 = explode(':', $ipstring);
  $num = array_reverse($ip6);  
  return $num;
}

Function largebin2floatA($binarystr) {
  $bits = str_split($binarystr, 16);
  $result = array();
  foreach ($bits as $bit) {
    array_push($result, (float)sprintf("%u", bindec($bit)));
  }
  $result = array_reverse($result);
  return $result;
}

Function decbin32 ($dec) {
  return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
}


/******************************************
This function takes 2 arguments, an IP address and a "range" in several
different formats.

Network ranges can be specified as:
* 1. Wildcard format:     1.2.3.*
* 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
* 3. Start-End IP format: 1.2.3.0-1.2.3.255

The function will return true if the supplied IP is within the range.
Note little validation is done on the range inputs - it expects you to
use one of the above 3 formats.
******************************************/
Function ip_in_range($ip, $range) {
 if (strpos($ip, '.') !== false) { 
  if (strpos($range, '/') !== false) {
    list($range, $netmask) = explode('/', $range, 2);
    if (strpos($netmask, '.') !== false) {
      $nets = explode('.', $netmask);
      while(count($nets) < 4) $nets[] = '*';
      $netmask = implode('.', $nets);
      $netmask = str_replace('*', '0', $netmask);
      $netmask_dec = ip2float($netmask);
		#printf("%-10s: %s\n", "Netmask", $netmask);
		#printf("%-10s: %-032b\n", "Netmaskbin", $netmask_dec);

    } else {
      $x = explode('.', $range);
      while(count($x)<4) $x[] = '0';
      #list($a,$b,$c,$d) = $x;
      #$range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
      $range = implode('.', $x);

      # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
      #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

      # Strategy 2 - Use math to create it
      $wildcard_dec = pow(2, (32-$netmask)) - 1;
      $netmask_dec = ~ $wildcard_dec;
      $netmask_dec = (float)(pow(2,32) - pow(2, (32-$netmask)));

    }
	#printf("%-10s: %s\n", "IP", $ip);
	#printf("%-10s: %-032b\n", "IPbin", ip2float($ip));
	#printf("%-10s: %s\n", "Range", $range);
	#printf("%-10s: %-032b\n", "Rangebin", ip2float($range));
	#printf("%-10s: %-032b\n", "Netmask", $netmask_dec);
	#    $a = float_and(ip2float($ip), $netmask_dec);
	#    $b = float_and(ip2float($range), $netmask_dec);
	#printf("%-10s: %-032b\n", "IP&MASK", $a);
	#printf("%-10s: %-032b\n", "Range&MASK", $b);
    return ( float_and(ip2float($ip), $netmask_dec) == float_and(ip2float($range), $netmask_dec) );
  } else {
    if (strpos($range, '*') !==false) { 
      $lower = str_replace('*', '0', $range);
      $upper = str_replace('*', '255', $range);
      $range = "$lower-$upper";
    }

    if (strpos($range, '-')!==false) { 
      list($lower, $upper) = explode('-', $range, 2);
      $lower_dec = ip2float($lower);
      $upper_dec = ip2float($upper);
      $ip_dec = ip2float($ip);
      return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
    }

    echo 'Range argument is not in 1.2.3.4/24 or 1.2.3.4/255.255.255.0 format';
    return false;
  }
 }
  if (strpos($ip, ':') !== false) { 
ini_set("display_errors", 1);
error_reporting(E_ALL);
    if (strpos($range, '/') !== false) {
      list($range, $netbits) = explode('/', $range, 2);
      $netmask_binstr = str_pad('', $netbits, '1') . str_pad('', 128-$netbits, '0');
    }
    if (preg_match('/::$/', $range)) {
      $range = preg_replace('/::$/', '', $range);
      $x = explode(':', $range);
      while(count($x) < 8) $x[] = '0';
      $range = implode(':', $x);
    }
    return ( largearray_and(ip6floatA($ip), largebin2floatA($netmask_binstr)) == largearray_and(ip6floatA($range), largebin2floatA($netmask_binstr)) );
  }

}
?>