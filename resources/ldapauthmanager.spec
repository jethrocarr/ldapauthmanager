Summary: LDAPAuthManager open source LDAP authentication management interface
Name: ldapauthmanager
Version: 1.2.0
Release: 1%{?dist}
License: AGPLv3
URL: http://www.amberdms.com/ldapauthmanager
Group: Applications/Internet
Source0: ldapauthmanager-%{version}.tar.bz2
BuildRoot: %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)
BuildArch: noarch
BuildRequires: gettext
Requires: httpd, mod_ssl
Requires: php >= 5.1.6, mysql-server, php-mysql, php-ldap, php-soap
Requires: perl, perl-DBD-MySQL
Prereq: httpd, php, mysql-server, php-mysql

%description
Provides the LDAPAuthManager web-based interface and SOAP API.


%package scripts
Summary:  Integration components for OpenLDAP servers such as logpush scripts.
Group: Applications/Internet

Requires: php-cli >= 5.1.6, php-soap

%description scripts
Provides application components that hook into OpenLDAP and provide services such as log upload to LDAPAuthManager.


%prep
%setup -q -n ldapauthmanager-%{version}

%build


%install
rm -rf $RPM_BUILD_ROOT
mkdir -p -m0755 $RPM_BUILD_ROOT%{_sysconfdir}/ldapauthmanager/
mkdir -p -m0755 $RPM_BUILD_ROOT%{_datadir}/ldapauthmanager/

# install application files and resources
cp -pr * $RPM_BUILD_ROOT%{_datadir}/ldapauthmanager/


# install www configuration file
install -m0700 htdocs/include/sample-config.php $RPM_BUILD_ROOT%{_sysconfdir}/ldapauthmanager/config.php
ln -s %{_sysconfdir}/ldapauthmanager/config.php $RPM_BUILD_ROOT%{_datadir}/ldapauthmanager/htdocs/include/config-settings.php


# install scripts configuration files
install -m0700 scripts/include/sample-config.php $RPM_BUILD_ROOT%{_sysconfdir}/ldapauthmanager/config-scripts.php
ln -s %{_sysconfdir}/ldapauthmanager/config-scripts.php $RPM_BUILD_ROOT%{_datadir}/ldapauthmanager/scripts/include/config-settings.php


# install the logpush bootscript
mkdir -p $RPM_BUILD_ROOT/etc/init.d/
install -m 755 resources/ldapauthmanager_logpush.rcsysinit $RPM_BUILD_ROOT/etc/init.d/ldapauthmanager_logpush

# install the apache configuration file
mkdir -p $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d
install -m 644 resources/ldapauthmanager-httpdconfig.conf $RPM_BUILD_ROOT%{_sysconfdir}/httpd/conf.d/ldapauthmanager.conf


%post

# Reload apache
echo "Reloading httpd..."
/etc/init.d/httpd reload

# update/install the MySQL DB
if [ $1 == 1 ];
then
	# install - requires manual user MySQL setup
	echo "Run cd %{_datadir}/ldapauthmanager/resources/; ./autoinstall.pl to install the SQL database."
else
	# upgrade - we can do it all automatically! :-)
	echo "Automatically upgrading the MySQL database..."
	%{_datadir}/ldapauthmanager/resources/schema_update.pl --schema=%{_datadir}/ldapauthmanager/sql/ -v
fi


%post scripts

if [ $1 == 0 ];
then
	# upgrading existing rpm
	echo "Restarting logging process..."
	/etc/init.d/ldapauthmanager_logpush restart
fi


%postun

# check if this is being removed for good, or just so that an
# upgrade can install.
if [ $1 == 0 ];
then
	# user needs to remove DB
	echo "LDAPAuthManager has been removed, but the MySQL database and user will need to be removed manually."
fi


%preun scripts

# stop running process
/etc/init.d/ldapauthmanager_logpush stop



%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-,root,root)
%config %dir %{_sysconfdir}/ldapauthmanager
%attr(770,root,apache) %config(noreplace) %{_sysconfdir}/ldapauthmanager/config.php
%attr(660,root,apache) %config(noreplace) %{_sysconfdir}/httpd/conf.d/ldapauthmanager.conf
%{_datadir}/ldapauthmanager/htdocs
%{_datadir}/ldapauthmanager/ldap
%{_datadir}/ldapauthmanager/resources
%{_datadir}/ldapauthmanager/sql
%{_datadir}/ldapauthmanager/radius

%doc %{_datadir}/ldapauthmanager/README
%doc %{_datadir}/ldapauthmanager/docs/AUTHORS
%doc %{_datadir}/ldapauthmanager/docs/CONTRIBUTORS
%doc %{_datadir}/ldapauthmanager/docs/COPYING


%files scripts
%defattr(-,root,root)
%config %dir %{_sysconfdir}/ldapauthmanager
%config(noreplace) %{_sysconfdir}/ldapauthmanager/config-scripts.php
%{_datadir}/ldapauthmanager/scripts
/etc/init.d/ldapauthmanager_logpush


%changelog
* Thu Mar 10 2011 Jethro Carr <jethro.carr@amberdms.com> 1.2.0
- Minor fixes, addition of plaintext password handling.
* Thu Mar 03 2011 Jethro Carr <jethro.carr@amberdms.com> 1.2.0_beta_1
- Implemented Mikrotik-specific vendor extensions.
* Fri Feb 25 2011 Jethro Carr <jethro.carr@amberdms.com> 1.1.0_beta_2
- Fixed bugs, added MD5 hash support, add LDAPv3, added TLS/SSL
* Sun Aug 01 2010 Jethro Carr <jethro.carr@amberdms.com> 1.1.0_beta_1
- Implemented new logging features
* Tue Apr 13 2010 Jethro Carr <jethro.carr@amberdms.com> 1.0.2
- Upgrade to inetOrgPerson
* Wed Mar 24 2010 Jethro Carr <jethro.carr@amberdms.com> 1.0.1
- Minor bug fixes, new features and 1.0.1 release
* Fri Mar 12 2010 Jethro Carr <jethro.carr@amberdms.com> 1.0.0
- Minor changes and 1.0.0 release.
* Wed Mar 10 2010 Jethro Carr <jethro.carr@amberdms.com> 1.0.0_beta_3
- Upgrade to include radius attribute configuration support on groups.
* Fri Feb 19 2010 Jethro Carr <jethro.carr@amberdms.com> 1.0.0_beta_2
- Upgrade to include radius attribute configuration support as optional feature.
* Wed Feb 03 2010 Jethro Carr <jethro.carr@amberdms.com> 1.0.0_beta_1
- Beta of first 1.0.0 release
* Mon Jan 25 2010 Jethro Carr <jethro.carr@amberdms.com> 1.0.0_alpha_1
- Inital Application release

