<!DOCTYPE html
   PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>{$title|escape}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <link rel="stylesheet" href="/css/styles.css" type="text/css" media="all" />

        <script type="text/javascript" src="/js/prototype.js"></script>
        <script type="text/javascript"
                src="/js/scriptaculous/scriptaculous.js"></script>
    </head>
    <body>
        <div id="header">
            <img src="/images/logo-print.gif" alt="" />
        </div>

        <div id="nav">
            <ul>
                <li{if $section == 'home'} class="active"{/if}>
                    <a href="{geturl controller='index'}">Home</a>
                </li>
                {if $authenticated}
                    <li{if $section == 'account'} class="active"{/if}>
                        <a href="{geturl controller='account'}">Your Account</a>
                    </li>
                    <li><a href="{geturl controller='account' action='logout'}">Logout</a></li>
                {else}
                    <li{if $section == 'register'} class="active"{/if}>
                        <a href="{geturl controller='account' action='register'}">Register</a>
                    </li>
                    <li{if $section == 'login'} class="active"{/if}>
                        <a href="{geturl controller='account' action='login'}">Login</a>
                    </li>
                {/if}
            </ul>
        </div>

        <div id="content-container" class="column">
            <div id="content">
                <div id="breadcrumbs">
                    {breadcrumbs trail=$breadcrumbs->getTrail() separator=' &raquo; '}
                </div>

                <h1>{$title|escape}</h1>
