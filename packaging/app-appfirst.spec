
Name: app-appfirst
Epoch: 1
Version: 1.0.2
Release: 1%{dist}
Summary: AppFirst System Monitor
License: GPLv3
Group: ClearOS/Apps
Packager: ClearCenter <developer@clearcenter.com>
Vendor: ClearCenter <developer@clearcenter.com>
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base

%description
Cloud-based network and server monitoring service delivers unified visibility from many data sources.  Custom dashboards, historical datasets, data correlation and the ability to easily graph data provides administrators with an essential tool to proactively manage the system.

%package core
Summary: AppFirst System Monitor - Core
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: appfirst

%description core
Cloud-based network and server monitoring service delivers unified visibility from many data sources.  Custom dashboards, historical datasets, data correlation and the ability to easily graph data provides administrators with an essential tool to proactively manage the system.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/appfirst
cp -r * %{buildroot}/usr/clearos/apps/appfirst/
rm -f %{buildroot}/usr/clearos/apps/appfirst/README.md
install -D -m 0640 packaging/AppFirst %{buildroot}/etc/AppFirst
install -D -m 0644 packaging/afcollector.php %{buildroot}/var/clearos/base/daemon/afcollector.php
install -D -m 0640 packaging/appfirst.conf %{buildroot}/etc/clearos/appfirst.conf

%post
logger -p local6.notice -t installer 'app-appfirst - installing'

%post core
logger -p local6.notice -t installer 'app-appfirst-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/appfirst/deploy/install ] && /usr/clearos/apps/appfirst/deploy/install
fi

[ -x /usr/clearos/apps/appfirst/deploy/upgrade ] && /usr/clearos/apps/appfirst/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-appfirst - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-appfirst-core - uninstalling'
    [ -x /usr/clearos/apps/appfirst/deploy/uninstall ] && /usr/clearos/apps/appfirst/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/appfirst/controllers
/usr/clearos/apps/appfirst/htdocs
/usr/clearos/apps/appfirst/views

%files core
%defattr(-,root,root)
%doc README.md
%exclude /usr/clearos/apps/appfirst/packaging
%dir /usr/clearos/apps/appfirst
/usr/clearos/apps/appfirst/deploy
/usr/clearos/apps/appfirst/language
/usr/clearos/apps/appfirst/libraries
%config(noreplace) /etc/AppFirst
/var/clearos/base/daemon/afcollector.php
%attr(0640,webconfig,webconfig) %config(noreplace) /etc/clearos/appfirst.conf
