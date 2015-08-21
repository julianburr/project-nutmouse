<?php

// Core classes
include_once(__DIR__ . "/Model.php");
include_once(__DIR__ . "/View.php");
include_once(__DIR__ . "/Controller.php");

// System classes
include_once(__DIR__ . "/System/Session.php");
include_once(__DIR__ . "/System/Config.php");
include_once(__DIR__ . "/System/Template.php");
include_once(__DIR__ . "/System/Action.php");
include_once(__DIR__ . "/System/HookPoint.php");
include_once(__DIR__ . "/System/Plugin.php");
include_once(__DIR__ . "/System/Meta.php");
include_once(__DIR__ . "/System/Cache.php");
include_once(__DIR__ . "/System/Locale.php");

// Model classes
include_once(__DIR__ . "/Model/Content.php");
include_once(__DIR__ . "/Model/User.php");

// Utilities e.g. for database interactions
include_once(__DIR__ . "/Utils/SqlManager.php");
include_once(__DIR__ . "/Utils/DateManager.php");
include_once(__DIR__ . "/Utils/FileManager.php");