<?php

// Core classes
include_once(__DIR__ . "/Model.php");
include_once(__DIR__ . "/View.php");
include_once(__DIR__ . "/Controller.php");

// System classes
include_once(__DIR__ . "/System/Action.php");
include_once(__DIR__ . "/System/HookPoint.php");
include_once(__DIR__ . "/System/Cache.php");
include_once(__DIR__ . "/System/AccessOfficer.php");
include_once(__DIR__ . "/System/Crypt.php");

// Model classes
include_once(__DIR__ . "/Model/Content.php");
include_once(__DIR__ . "/Model/User.php");
include_once(__DIR__ . "/Model/Locale.php");
include_once(__DIR__ . "/Model/Log.php");
include_once(__DIR__ . "/Model/Meta.php");
include_once(__DIR__ . "/Model/Config.php");
include_once(__DIR__ . "/Model/Session.php");
include_once(__DIR__ . "/Model/Plugin.php");
include_once(__DIR__ . "/Model/Version.php");

// Utilities e.g. for database interactions
include_once(__DIR__ . "/Utils/SqlManager.php");
include_once(__DIR__ . "/Utils/DateManager.php");
include_once(__DIR__ . "/Utils/FileManager.php");
include_once(__DIR__ . "/Utils/FormManager.php");