<?php
/*
	inc_radius.php

	Provides functions for working with radius attributes and other data.
*/



/*
	radius_attr_standard

	Returns an array of all the standard radius attributes.

	TODO: this is currently set here in the code, but a better solution for the future would be
	      to query the LDAP database to determine all possible attributes.

	Values
	array		Array of standard attributes
*/

function radius_attr_standard()
{
	log_debug("inc_radius", "Executing radius_attr_standard()");

	$attributes = array('radiusArapFeatures', 'radiusArapSecurity', 'radiusArapZoneAccess', 'radiusAuthType', 'radiusCallbackId', 'radiusCallbackNumber', 'radiusCalledStationId', 'radiusCallingStationId', 'radiusClass', 'radiusClientIPAddress', 'radiusFilterId', 'radiusFramedAppleTalkLink', 'radiusFramedAppleTalkNetwork', 'radiusFramedAppleTalkZone', 'radiusFramedCompression', 'radiusFramedIPAddress', 'radiusFramedIPNetmask', 'radiusFramedIPXNetwork', 'radiusFramedMTU', 'radiusFramedProtocol', 'radiusFramedRoute', 'radiusFramedRouting', 'radiusGroupName', 'radiusHint', 'radiusHuntgroupName', 'radiusIdleTimeout', 'radiusLoginIPHost', 'radiusLoginLATGroup', 'radiusLoginLATNode', 'radiusLoginLATPort', 'radiusLoginLATService', 'radiusLoginService', 'radiusLoginTCPPort', 'radiusPasswordRetry', 'radiusPortLimit', 'radiusProfileDn', 'radiusPrompt', 'radiusProxyToRealm', 'radiusReplicateToRealm', 'radiusRealm', 'radiusServiceType', 'radiusSessionTimeout', 'radiusTerminationAction', 'radiusTunnelAssignmentId', 'radiusTunnelMediumType', 'radiusTunnelPassword', 'radiusTunnelPreference', 'radiusTunnelPrivateGroupId', 'radiusTunnelServerEndpoint', 'radiusTunnelType', 'radiusVSA', 'radiusTunnelClientEndpoint', 'radiusSimultaneousUse', 'radiusLoginTime', 'radiusUserCategory', 'radiusStripUserName', 'dialupAccess', 'radiusExpiration', 'radiusNASIpAddress', 'radiusReplyMessage');

	return $attributes;

} // end of radius_attr_standard





/*
	radius_attr_mikrotik

	Returns an associative array of the Mikrotik device attributes
	and their types.

	Values
	array		Array of attribute names
*/

function radius_attr_mikrotik()
{
	log_debug("inc_radius", "Executing radius_attr_mikrotik()");

	$attributes = array(
			'Mikrotik-Recv-Limit'			=> 'bytes',
			'Mikrotik-Recv-Limit-Gigawords'		=> 'gigaword',
			'Mikrotik-Xmit-Limit'			=> 'bytes',
			'Mikrotik-Xmit-Limit-Gigawords'		=> 'gigaword',
			'Mikrotik-Total-Limit'			=> 'bytes',
			'Mikrotik-Total-Limit-Gigawords'	=> 'gigaword',
			'Mikrotik-Rate-Limit'			=> 'string',
			'Mikrotik-Group'			=> 'string',
			'Mikrotik-Realm'			=> 'string',
			'Mikrotik-Host-IP'			=> 'ipaddr',
			'Mikrotik-Mark-Id'			=> 'string',
			'Mikrotik-Advertise-URL'		=> 'string',
			'Mikrotik-Advertise-Interval'		=> 'int',
			'Mikrotik-Address-List'			=> 'string',
			'Mikrotik-Wireless-PSK'			=> 'string',
			'Mikrotik-Wireless-Forward'		=> 'bool',
			'Mikrotik-Wireless-Skip-Dot1x'		=> 'bool',
			'Mikrotik-Wireless-Enc-Algo'		=> 'int',
			'Mikrotik-Wireless-Enc-Key'		=> 'string',
			'Mikrotik-Wireless-MPKey'		=> 'string',
			'Mikrotik-Wireless-Comment'		=> 'string'
		);

	return $attributes;

} // end of radius_attr_mikrotik







?>
