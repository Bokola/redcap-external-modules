<?php
namespace ExternalModules;
require_once dirname(__FILE__) . '/../../classes/ExternalModules.php';

use Exception;

ExternalModules::addResource('css/style.css');

$sql = ExternalModules::getSqlToRunIfDBOutdated();
if($sql !== ""){
	echo '<p>Your current database table structure does not match REDCap\'s expected table structure for External Modules, which means that database tables and/or parts of tables are missing. Copy the SQL in the box below and execute it in the MySQL database named '.$db.' where the REDCap database tables are stored. Once the SQL has been executed, reload this page to run this check again.</p>';
	echo '<textarea style="width: 100%; height: 300px" onclick="this.focus();this.select()" readonly="readonly">' . $sql . '</textarea>';
	return;
}

$pid = $_GET['pid'];
?>

<div id="external-modules-download" class="simpleDialog" role="dialog">
	Do you wish to download the External Module named 
	"<b><?php print \RCView::escape(rawurldecode(urldecode($_GET['download_module_title']))) ?></b>"?
	This will create a new directory folder for the module on the REDCap web server.
</div>

<div id="external-modules-disabled-modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Available Modules</h4>
			</div>
			<div class="modal-body">
				<form>
				</form>
			</div>
		</div>
	</div>
</div>

<div id="external-modules-usage-modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"></h4>
			</div>
			<div class="modal-body">
			</div>
		</div>
	</div>
</div>

<br>
<?php if (isset($_GET['pid'])) { ?>

<p>External modules combine and replace what REDCap previously has called plugins and hooks.
Below is a list of enabled modules that can be used in this project. You can see what other modules are
available by searching for additional modules. These are groups of code from outside sources
that enhance REDCap functioning for specific purposes.</p> 

<?php } else { ?>

<p>External modules combine and replace what REDCap previously has called plugins and hooks.
Below is a list of enabled modules (consisting of hooks and plugins) that are available for your users' use.
They can be enabled system-wide or they can be enabled (opt-in style) on a project-level. Default values for each module,
where desired, have been set by the author of the module. Each system can override these defaults by configuring them
here. In turn, each project can override this set of defaults with their own value.</p>

<?php } ?>
<br>
<?php if(SUPER_USER) { ?>
	<button id="external-modules-enable-modules-button" class="btn btn-success btn-sm">
		<span class="glyphicon glyphicon-off" aria-hidden="true"></span>
		Enable a module
	</button>
<?php } ?>
<?php if (SUPER_USER && !isset($_GET['pid'])) { ?>
	<button id="external-modules-download-modules-button" class="btn btn-primary btn-sm">
		<span class="glyphicon glyphicon-save" aria-hidden="true"></span>
		Download new module from repository
	</button>
<?php } ?>
<br>
<br>

<?php if (isset($_GET['pid'])) { ?>
<h3>Currently Enabled Modules</h3>
<?php } else { ?>
<h3>Modules Currently Available on this System</h3>
<?php } ?>

<script>
	var override = '<?=ExternalModules::OVERRIDE_PERMISSION_LEVEL_DESIGN_USERS?>';
	var enabled = '<?=ExternalModules::KEY_ENABLED?>';
	var overrideSuffix = '<?=ExternalModules::OVERRIDE_PERMISSION_LEVEL_SUFFIX?>';
</script>

<table id='external-modules-enabled' class="table">
	<?php

	$versionsByPrefix = ExternalModules::getEnabledModules($_GET['pid']);
	$configsByPrefix = array();

	if (empty($versionsByPrefix)) {
		echo 'None';
	} else {
		foreach ($versionsByPrefix as $prefix => $version) {
			if (isset($_GET['pid'])) {
				$config = ExternalModules::getConfig($prefix, $version, $_GET['pid']);
			} else {
				$config = ExternalModules::getConfig($prefix, $version);
			}

			## Add resources for custom javascript fields
			foreach(array_merge($config['project-settings'],$config['system-settings']) as $configRow) {
				if($configRow['source']) {
					$sources = explode(",",$configRow['source']);
					foreach($sources as $sourceLocation) {
						if(is_file(ExternalModules::getModuleDirectoryPath($prefix,$version)."/".$sourceLocation)) {
							// include file from module directory
							ExternalModules::addResource(ExternalModules::getModuleDirectoryUrl($prefix,$version)."/".$sourceLocation);
						}
						else if(is_file(dirname(__DIR__)."/js/".$sourceLocation)) {
							// include file from external_modules directory
							ExternalModules::addResource("js/".$sourceLocation);
						}
					}
				}
			}


			$configsByPrefix[$prefix] = $config;
			$enabled = false;
			if (isset($_GET['pid'])) {
				$enabled = ExternalModules::getProjectSetting($prefix, $_GET['pid'], ExternalModules::KEY_ENABLED);
			}
			if ((isset($_GET['pid']) && $enabled) || (!isset($_GET['pid']) && isset($config['system-settings']))) {
			?>
				<tr data-module='<?= $prefix ?>' data-version='<?= $version ?>'>
					<td><div class='external-modules-title'><?= $config['name'] . ' - ' . $version ?></div><div class='external-modules-description'><?php echo $config['description'] ? $config['description'] : ''; ?></div><div class='external-modules-byline'>
<?php
        if ($config['authors']) {
                $names = array();
                foreach ($config['authors'] as $author) {
                        $name = $author['name'];
                        if ($name) {
                                if ($author['email']) {
                                        $names[] = "<a href='mailto:".$author['email']."'>".$name."</a>";
                                } else {
                                        $names[] = $name;
                                }
                        }
                }
                if (count($names) > 0) {
                        echo "by ".implode($names, ", ");
                }
        }
?>
</div></td>
					<td class="external-modules-action-buttons">
						<?php if(ExternalModules::isProjectSettingsConfigOverwrittenBySystem($config) || !empty($config['project-settings'])){?>
							<button class='external-modules-configure-button'>Configure</button>
						<?php } ?>
						<?php if(SUPER_USER) { ?>
							<button class='external-modules-disable-button'>Disable</button>
						<?php } ?>
						<?php if(!isset($_GET['pid'])) { ?>
							<button class='external-modules-usage-button' style="min-width: 90px">View Usage</button>
						<?php } ?>
					</td>
				</tr>
			<?php
			}
		}
	}

	?>
</table>

<?php
global $configsByPrefixJSON,$versionsByPrefixJSON;

// JSON_PARTIAL_OUTPUT_ON_ERROR was added here to fix an odd conflict between field-list and form-list types
// and some Hebrew characters on the "Israel: Healthcare Personnel (Hebrew)" project that could not be json_encoded.
// This workaround allows configs to be encoded anyway, even though the unencodable characters will be excluded
// (causing form-list and field-list to not work for any fields with unencodeable characters).
// I spent a couple of hours trying to find a solution, but was unable.  This workaround will have to do for now.
$configsByPrefixJSON = json_encode($configsByPrefix, JSON_PARTIAL_OUTPUT_ON_ERROR);
if($configsByPrefixJSON === false){
	echo '<script>alert(' . json_encode('An error occurred while converting the configurations to JSON: ' . json_last_error_msg()) . ');</script>';
	throw new Exception('An error occurred while converting the configurations to JSON: ' . json_last_error_msg());
}

$versionsByPrefixJSON = json_encode($versionsByPrefix, JSON_PARTIAL_OUTPUT_ON_ERROR);
if($versionsByPrefixJSON == null){
	echo '<script>alert(' . json_encode('An error occurred while converting the versions to JSON: ' . json_last_error_msg()) . ');</script>';
	throw new Exception("An error occurred while converting the versions to JSON: " . json_last_error_msg());
}

require_once 'globals.php';

?>
<script>
	ExternalModules.sortModuleTable($('#external-modules-enabled'))
</script>
