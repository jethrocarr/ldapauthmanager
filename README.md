# LDAPAuthManager

**Please note: I consider this project deprecated, the code remains here for reference. FreeIPA is probably a better replacement for most needs.**


## Project Homepage

For more information including source code, issue tracker and documentation
visit the project homepage:

https://github.com/jethrocarr/ldapauthmanager


## Introduction

LDAPAuthManager is an open source (AGPL) web-based application that can connect
to an LDAP database and provide user and group management.

LDAPAuthManager has been designed to make LDAP user authentication easy and
quick to deploy as well as offering a place for regular users to change their
passwords.


## Key Features

* Fast, clean, web-based interface
* Designed specifically for LDAP authentication databases
* POSIX User/Group Management with UID/GID autoincrement and sanity checking
* Zone/LDAP Group functionality to allow zoning of user information (eg zone-web, zone-secure) to restrict what users are exposed to what systems.
* Log read & AJAX display feature for easy debugging.
* User password change feature via web interface.
* Support for SSHA passwords as well as plaintext for systems that require it such as Radius CHAP.
* Supports OpenLDAP 2.3+
* Features to manage attributes for FreeRadius
* Documentation

