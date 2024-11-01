<div aria-live="polite" aria-atomic="true" style="position: relative; min-height: 200px;">
	<div style="position: absolute; top: 0; right: 0;">
		<div class="toast bg-success text-dark" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
			<div class="toast-header bg-success text-white">
				<strong class="mr-auto">Arvia Meeting</strong>
				<button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
		</div>
	</div>
</div>
<div class="container">
	<div id="controlsContainer" class="row justify-content-center">
		<div class="col-6">
			<div class="text-center mB-20">
				<?php require 'logo.php'; ?>
			</div>
			<?php
			if (!empty($error)) {
			?>
				<div class="h5 py-1">Errors:</div>
				<div class="wrap">
					<?php
					if (is_array($error)) {
						foreach ($error as $e) {
							_e('<div style="color: red">' . $e . '</div>');
						}
					} else {
						_e('<div style="color: red">' . $error . '</div>');
					}
					unset($error);
					?>
				</div>
			<?php
			}
			?>
		</div>
	</div>