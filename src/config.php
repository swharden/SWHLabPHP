<?php

/* ########## SETTINGS ###############################
 * adjust these to reflect your system
*/

// path to SWHLabPHP main folder (with respect to htdocs)
$WEBPATH_SWHLABPHP = "/SWHLabPHP";

// path to SWHLabPHP main folder (with respect to C:)
$PATH_SWHLABPHP = 'C:\Apache24\htdocs\SWHLabPHP';

// GitHub project: SWHarden / swharden/SWHLabPHP
$PATH_SWHLAB_ROOT = 'C:\Users\LabAdmin\Documents\GitHub\SWHLab';

// GitHub project: swharden/ROI-Analysis-Pipeline
$PATH_ROI_ROOT = 'C:\Users\LabAdmin\Documents\GitHub\ROI-Analysis-Pipeline';

// path to python
$PATH_PYTHON = 'C:\ProgramData\Anaconda3\python.exe';

// customizable settings
$template="01_barebones";

// this is for rewriting \\network\paths\ to web-safe served with aliased virtual directories
$fileReplacements[]=["\\\\Spike\\","\\\\spike\\"]; // caps matter in apache, not Windows.
$fileReplacements[]=["\\\\spike\\X_Drive\\Data\\","/dataX/"];
$fileReplacements[]=["\\\\SPIKE\\X_DRIVE\\Data\\","/dataX/"];
$fileReplacements[]=["D:\\Data\\","/data/"];
$fileReplacements[]=["\\\\192.168.1.100\\X_Mirror\\Data\\","/dataX/"];
$fileReplacements[]=["\\\\192.168.1.100\\X_MIRROR\\Data\\","/dataX/"];

// network path of active X-Drive folder
$PATH_XDRIVE_ROOT="\\\\Spike\\X_Drive\\";
//$PATH_XDRIVE_ROOT="\\\\192.168.1.100\\X_Mirror\\";


/* ########## AUTOMATIC VARIABLE CREATION ###############################
 * these are not intended to be modified by hand
*/
$PATH_SWHLAB_PROTOCOLS = $PATH_SWHLAB_ROOT.'\swhlab\analysis\protocols.py';
$PATH_ROI_PYLINESCAN = $PATH_ROI_ROOT.'\pyLS\pyLineScan.py';
$PATH_COMMAND_PROCESS = $PATH_SWHLABPHP.'\python\process.py';
$PATH_COMMAND_LIST = $PATH_SWHLABPHP.'\python\COMMAND_LIST.txt';
$PATH_COMMAND_LOG = $PATH_SWHLABPHP.'\python\COMMAND_LOG.json';
$PATH_COMMAND_ERROR = $PATH_SWHLABPHP.'\python\COMMAND_ERROR.json';
?>