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
}



?>
