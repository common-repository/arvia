<?php

?>
<div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 200px;">
	<div id="toast-container" style="position: absolute; top: 0; right: 0;">
	</div>
</div>
<div class="container justify-content-center">
	<div class="row justify-content-center">
		<div class="text-center mB-20 col-6">
			<?php require 'logo.php'; ?>
		</div>
	</div>
	<div class="row justify-content-center">
		<div id="controlsContainer" class="d-none col-6">
			<div class="row justify-content-center">
				<div class="h5 py-3 col">Please select your meeting project to add on your web site.</div>
			</div>
			<div class="row">
				<div class="col">
					<form>
						<div class="form-group">
							<label for="projectList" class=" col-form-label">Project</label>
							<select id="projectList" name="projectId" class="form-control" style="max-width: 100% !important;">
								<option>
								</option>
							</select>
						</div>
				</div>
			</div>

			<div class="row">
				<div class="col-12 mt-3">
					<div class="float-right">
						<button id="save_button" type="button" class="btn btn-primary mL-5">Save</button>
					</div>
				</div>
			</div>
			</form>
		</div>

		<div id="loadingContainer" class="row justify-content-center mt-3">
			<?php require 'loading.php'; ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	const $ = jQuery.noConflict();

	function showToast(toastBody, color) {
		const delay = 3000;
		const html = `<div class="toast text-dark" role="alert" aria-live="assertive" aria-atomic="true">
			<div class="toast-header bg-${color} text-white">
				<strong class="mr-auto">Arvia Meeting</strong>
			</div>
			<div class="toast-body bg-${color} text-white">${toastBody}</div>
		</div>`;
		const toastElement = htmlToElement(html);
		const toastConainerElement = document.getElementById("toast-container");
		toastConainerElement.appendChild(toastElement);
		const toast = new bootstrap.Toast(toastElement, {
			delay: delay,
			animation: true
		});
		toast.show();

		setTimeout(() => toastElement.remove(), delay + 3000);
	}

	function htmlToElement(html) {
		const template = document.createElement('template');
		html = html.trim();
		template.innerHTML = html;
		return template.content.firstChild;
	}

	const token = '<?php _e(get_option("arviachat_token")) ?>';
	const arviaUrl = '<?php _e(ARVIA_INTEGRATION_URL) ?>';
	let projectListData;
	let projectList, saveButton;
	let selectedProjectId = '<?php _e(get_option("arviachat_projectId")) ?>';

	async function onSaveButtonClick() {
		try {
			if (!selectedProjectId) {
				showToast("Please select project", "danger");
				return;
			}
			const formData = new FormData();
			formData.append('projectId', selectedProjectId);
			formData.append('integration', 'wordpress');
			disableControls(true);
			const response = await fetch('', {
				method: 'POST',
				body: formData
			});

			disableControls(false);

			const d = await response.text();
			console.log(d);
			const i = d.indexOf('arviaIntegrationMessage')
			if (i == -1) {
				showToast("Something went wrong", "danger");
			}
			const s = i - 2;
			e = d.indexOf('}', s) + 1;
			m = d.substring(s, e);
			const j = JSON.parse(m);

			if (j["success"]) {
				showToast(j["arviaIntegrationMessage"], "success");
			} else {
				showToast(j["arviaIntegrationMessage"], "danger");
			}
		} catch (err) {
			showToast(err.message, "danger");
			disableControls(false);
			console.error(err);
		}
	}

	function disableControls(val) {
		projectList.disabled = val;
		saveButton.disabled = val;
		if (val) {
			controlsContainer.classList.add('d-none');
			loadingContainer.classList.remove('d-none');
		} else {
			controlsContainer.classList.remove('d-none');
			loadingContainer.classList.add('d-none');
		}
	}

	async function init() {
		saveButton = document.getElementById('save_button');
		saveButton.addEventListener('click', onSaveButtonClick);
		projectList = document.getElementById('projectList');
		projectList.addEventListener('change', onProjectListChange)
		controlsContainer = document.getElementById('controlsContainer')
		loadingContainer = document.getElementById('loadingContainer')
		disableControls(true);
		await loadProjects();
		disableControls(false);
	}

	function fillProjectList() {
		projectList.innerHTML = '';
		projectListData.forEach(x => {
			projectList.innerHTML += `<option value="${x.id}" ${x.id == selectedProjectId ? 'selected' : ''}>${x.name}</option>`;
		})
	}


	async function loadProjects() {
		try {
			const res = await fetch(`${arviaUrl}/api/projects`, {
				headers: {
					'Authorization': `Bearer ${token}`
				},
			})
			projectListData = (await res.json()).map(x => ({
				id: x._id,
				name: x.name
			}));
			if (!selectedProjectId)
				selectedProjectId = projectListData[0].id;

			fillProjectList();
		} catch (err) {
			console.error(err);
		}
	}

	async function onProjectListChange(e) {
		try {
			disableControls(true);
			selectedProjectId = e.target.value;
			disableControls(false);
		} catch (err) {
			console.error(err);
		}
	}

	if (document.readyState !== 'loading') {
		init();
	} else {
		document.addEventListener('DOMContentLoaded', function() {
			init();
		});
	}
</script>