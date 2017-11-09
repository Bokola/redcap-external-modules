<?php
namespace ExternalModules;
require_once __DIR__ . '/../classes/ExternalModules.php';
require_once APP_PATH_DOCROOT . 'ControlCenter/header.php';

?>

<h4 style="margin-top:0;" class="clearfix">
	<div class="pull-left">
		<img src='../images/puzzle_medium.png'>
		External Modules - Module Manager
	</div>	
	<div class="pull-right" style="margin-top:5px;">
		<button id="external-modules-add-custom-text-button" class="btn btn-defaultrc btn-xs">
			<span class="glyphicon glyphicon-pencil" aria-hidden="true"></span>
			Set custom text for Project Module Manager page
		</button>
	</div>
</h4>

<div id="external-modules-custom-text-dialog" class="simpleDialog" role="dialog">
	You may optionally provide custom text in the text box below that will appear to users on the External Modules "Project Module Manager" page in each project.
	It may be useful to provide some custom text to users for any of the following reasons: 
	1) To make users aware of institutional policies or procedures required before an administrator can enable a module, 
	2) To display guidelines (or a link to an external page with guidelines) regarding the usage of particular modules at your institution, 
	or 3) To bring to the user's attention anything that might be helpful regarding particular modules or External Modules in general.
	<br><br><b>Custom text displayed on Project Module Manager page:</b><br>
	<textarea id="external_modules_project_custom_text" class="x-form-field notesbox"><?=htmlspecialchars($external_modules_project_custom_text, ENT_QUOTES)?></textarea>
	<div class="cc_info">NOTE: HTML may be used in order to adjust the style of the text or to display links, images, etc.</div>
</div>

<?php
ExternalModules::safeRequireOnce('manager/templates/enabled-modules.php');
?>

<div id="external-modules-enable-modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close close-button" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Enable Module: <span class="module-name"></span></h4>
			</div>
			<div class="modal-body">
				<div id="external-modules-enable-modal-error"></div>
				<p>This module requests the following permissions:</p>
				<ul></ul>
			</div>
			<div class="modal-footer">
				<button class="close-button" data-dismiss="modal">Cancel</button>
				<button class="enable-button"></button>
			</div>
		</div>
	</div>
</div>

<div id="external-modules-configure-modal" class="modal fade" role="dialog" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Configure Module: <span class="module-name"></span></h4>
			</div>
			<div class="modal-body">
				<table class="table table-no-top-row-border">
					<thead>
						<tr>
							<th colspan="3">System Settings for All Projects</th>
							<th>Project Override<br>Permission Level</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
			<div class="modal-footer">
				<button data-dismiss="modal">Cancel</button>
				<button class="save">Save</button>
			</div>
		</div>
	</div>
</div>

<?php ExternalModules::addResource(ExternalModules::getManagerJSDirectory().'spin.js'); ?>
<?php ExternalModules::addResource(ExternalModules::getManagerJSDirectory().'control_center.js'); ?>

<?php

require_once APP_PATH_DOCROOT . 'ControlCenter/footer.php';
