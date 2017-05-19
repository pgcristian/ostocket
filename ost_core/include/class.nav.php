<?php

class StaffNav {
    var $activetab;
    var $activeMenu;
    var $panel;
    var $staff;
    function StaffNav($staff, $panel = 'staff') {
        $this->staff = $staff;
        $this->panel = strtolower($panel);
    }
    function __get($what) {
        switch ($what) {
            case 'tabs':
                $this->tabs = $this->getTabs();
                break;
            case 'submenus':
                $this->submenus = $this->getSubMenus();
                break;
            default:
                throw new Exception($what . ': No such attribute');
        }
        return $this->{$what};
    }
    function getPanel() {
        return $this->panel;
    }
    function isAdminPanel() {
        return (!strcasecmp($this->getPanel(),
                        'admin'));
    }
    function isStaffPanel() {
        return (!$this->isAdminPanel());
    }
    function getRegisteredApps() {
        return Application::getStaffApps();
    }
    function setTabActive($tab, $menu = '') {
        if ($this->tabs[$tab]) {
            $this->tabs[$tab]['active'] = true;
            if ($this->activetab && $this->activetab != $tab && $this->tabs[$this->activetab])
                $this->tabs[$this->activetab]['active'] = false;

            $this->activetab = $tab;
            if ($menu)
                $this->setActiveSubMenu($menu,
                        $tab);

            return true;
        }
        return false;
    }
    function setActiveTab($tab, $menu = '') {
        return $this->setTabActive($tab,
                        $menu);
    }
    function getActiveTab() {
        return $this->activetab;
    }
    function setActiveSubMenu($mid, $tab = '') {
        if (is_numeric($mid))
            $this->activeMenu = $mid;
        elseif ($mid && $tab && ($subNav = $this->getSubNav($tab))) {
            foreach ($subNav as $k => $menu) {
                if (strcasecmp($mid,
                                $menu['href']))
                    continue;
                $this->activeMenu = $k + 1;
                break;
            }
        }
    }
    function getActiveMenu() {
        return $this->activeMenu;
    }
    function addSubMenu($item, $active = false) {
        isset($this->submenus[$this->getPanel() . '.' . $this->activetab]);
        $this->submenus[$this->getPanel() . '.' . $this->activetab][] = $item;
        if ($active)
            $this->activeMenu = sizeof($this->submenus[$this->getPanel() . '.' . $this->activetab]);
    }
    function getTabs() {
        if (!$this->tabs) {
            $this->tabs = array();
            $this->tabs['dashboard'] = array('desc' => __('Dashboard'), 'href' => 'dashboard.php',
                'title' => __('Agent Dashboard'));
            $this->tabs['users'] = array('desc' => __('Users'), 'href' => 'users.php',
                'title' => __('User Directory'));
            $this->tabs['tickets'] = array('desc' => __('Tickets'), 'href' => 'tickets.php',
                'title' => __('Ticket Queue'));
            $this->tabs['kbase'] = array('desc' => __('Knowledgebase'), 'href' => 'kb.php',
                'title' => __('Knowledgebase'));
            if (count($this->getRegisteredApps()))
                $this->tabs['apps'] = array('desc' => __('Applications'), 'href' => 'apps.php',
                    'title' => __('Applications'));
        }
        return $this->tabs;
    }
    function getSubMenus() {
        $staff = $this->staff;
        $submenus = array();
        foreach ($this->getTabs() as $k => $tab) {
            $subnav = array();
            switch (strtolower($k)) {
                case 'tickets':
                    $subnav[] = array('desc' => __('Tickets'), 'href' => 'tickets.php',
                        'iconclass' => 'Ticket', 'droponly' => true);
                    if ($staff) {
                        if (($assigned = $staff->getNumAssignedTickets()))
                            $subnav[] = array('desc' => __('My&nbsp;Tickets') . " ($assigned)",
                                'href' => 'tickets.php?status=assigned',
                                'iconclass' => 'assignedTickets',
                                'droponly' => true);

                        if ($staff->canCreateTickets())
                            $subnav[] = array('desc' => __('New Ticket'),
                                'title' => __('Open a New Ticket'),
                                'href' => 'tickets.php?a=open',
                                'iconclass' => 'newTicket',
                                'id' => 'new-ticket',
                                'droponly' => true);
                    }
                    break;
                case 'dashboard':
                    $subnav[] = array('desc' => __('Dashboard'), 'href' => 'dashboard.php',
                        'iconclass' => 'logs');
                    $subnav[] = array('desc' => __('Agent Directory'), 'href' => 'directory.php',
                        'iconclass' => 'teams');
                    $subnav[] = array('desc' => __('My Profile'), 'href' => 'profile.php',
                        'iconclass' => 'users');
                    break;
                case 'users':
                    $subnav[] = array('desc' => __('User Directory'), 'href' => 'users.php',
                        'iconclass' => 'teams');
                    $subnav[] = array('desc' => __('Organizations'), 'href' => 'orgs.php',
                        'iconclass' => 'departments');
                    break;
                case 'kbase':
                    $subnav[] = array('desc' => __('FAQs'), 'href' => 'kb.php', 'urls' => array(
                            'faq.php'), 'iconclass' => 'kb');
                    if ($staff) {
                        if ($staff->canManageFAQ())
                            $subnav[] = array('desc' => __('Categories'), 'href' => 'categories.php',
                                'iconclass' => 'faq-categories');
                        if ($staff->canManageCannedResponses())
                            $subnav[] = array('desc' => __('Canned Responses'), 'href' => 'canned.php',
                                'iconclass' => 'canned');
                    }
                    break;
                case 'apps':
                    foreach ($this->getRegisteredApps() as $app)
                        $subnav[] = $app;
                    break;
            }
            if ($subnav)
                $submenus[$this->getPanel() . '.' . strtolower($k)] = $subnav;
        }
        return $submenus;
    }
    function getSubMenu($tab = null) {
        $tab = $tab ? $tab : $this->activetab;
        return $this->submenus[$this->getPanel() . '.' . $tab];
    }
    function getSubNav($tab = null) {
        return $this->getSubMenu($tab);
    }
}