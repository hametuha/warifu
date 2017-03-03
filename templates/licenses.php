<!DOCTYPE html>
<html <?php language_attributes() ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ) ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php esc_html_e( 'Your Product Licenses', 'warifu' ) ?> - <?php bloginfo( 'name' ) ?></title>

	<link rel="stylesheet" href="<?php echo esc_attr( warifu_asset_path( 'assets/lumx.css?version='.WARIFU_VERSION ) ) ?>"/>
	<link rel="stylesheet" href="<?php echo esc_attr( warifu_asset_path( 'assets/css/style.css?version='.WARIFU_VERSION ) ) ?>"/>
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700">
	<?php
	/**
	 * Action hook for license page.
	 *
	 * @action warifu_license_page_head
	 */
	do_action( 'warifu_license_page_head' );
	?>
</head>
<body ng-app="warifu">

<header>
	<div class="inner">
		<a lx-ripple lx-icon="" lx-color="blue" lx-type="icon" class="btn btn--m btn--blue btn--icon"
		   href="<?php echo esc_url( warifu_return_url() ) ?>" style="position: relative; overflow: hidden;">
			<span class="icon mdi mdi-chevron-left"></span>
		</a>
		<h1>
			<?php esc_html_e( 'Your Product Licenses', 'warifu' ) ?>
			<small><?php bloginfo( 'name' ) ?></small>
		</h1>
	</div>
</header>

<div class="main">

	<div class="wrapper inner" ng-cloak
	     ng-controller="warifuManager"
	     ng-init="init('<?php echo esc_attr( warifu_api_url() ) ?>', '<?php echo wp_create_nonce('wp_rest') ?>')">

		<p class="warifu-license-total">
			<span ng-if="!total">
				<?php _e( 'You have no license.', 'warifu' ) ?>
			</span>
			<span ng-if="1 === total">
				<?php _e( 'You have 1 license.', 'warifu' ) ?>
			</span>
			<span ng-if="1 < total">
				<?php printf( __( 'You have %s licenses.', 'warifu' ), '{{total}}' ) ?>
			</span>
		</p>

		<div class="license-list" ng-class="loading ? 'loading' : ''">
			<ul class="list">
				<li class="list-row list-row--has-separator" ng-repeat="license in licenses">
					<div class="list-row__content">
						<span class="display-block">
							{{license.license}}
						</span>
						<span class="display-block fs-body-1 tc-black-2">
							{{license.title}}
						</span>
					</div>
					<div class="list-row__secondary">
						<lx-fab lx-direction="left">
							<lx-fab-trigger>
								<lx-button lx-size="xl" lx-type="fab" lx-color="black" lx-tooltip="<?php esc_attr_e( 'Actions', 'warifu' ) ?>" lx-tooltip-position="top">
									<i style="color: #fff" class="mdi mdi-wrench"></i>
									<i style="color: #fff" class="mdi mdi-close"></i>
								</lx-button>
							</lx-fab-trigger>

							<lx-fab-actions>
								<lx-button lx-size="l" lx-color="red" lx-type="fab" lx-tooltip="<?php esc_attr_e( 'Delete', 'warifu' ) ?>" lx-tooltip-position="top"
									       ng-click="removeLicense(license.key)">
									<i style="color: #fff" class="mdi mdi-delete"></i>
								</lx-button>

								<lx-button lx-size="l" lx-color="teal" lx-type="fab" lx-tooltip="<?php esc_attr_e( 'Validate', 'warifu' ) ?>" lx-tooltip-position="top"
								           ng-click="validate(license.license, license.id)">
									<i style="color: #fff" class="mdi mdi-checkbox-marked-circle-outline"></i>
								</lx-button>

								<lx-button lx-size="l" lx-color="amber" lx-type="fab" lx-tooltip="<?php esc_attr_e( 'View Product', 'warifu' ) ?>" lx-tooltip-position="top"
								           ng-href="{{license.url}}">
									<i style="color: #fff" class="mdi mdi-eye"></i>
								</lx-button>

							</lx-fab-actions>
						</lx-fab>
					</div>
				</li>
			</ul>
		</div>


		<lx-dialog id="license-dialog" ng-cloak>
			<lx-dialog-header>
				<div class="toolbar bgc-green-400 pl++">
            <span class="toolbar__label tc-white fs-title">
                <?php _e( 'License Validation Result', 'warifu' ) ?>
            </span>
				</div>

			</lx-dialog-header>

			<lx-dialog-content>
				<table class="license-table">
					<tbody>
					<tr ng-repeat="resultRow in validateResult track by $index">
						<th>{{resultRow.title}}</th>
						<td>{{resultRow.value}}</td>
					</tr>
					</tbody>
				</table>
			</lx-dialog-content>

			<lx-dialog-footer>
				<lx-button lx-type="flat" lx-dialog-close><?php _e( 'O.K.', 'warifu' ) ?></lx-button>
			</lx-dialog-footer>

		</lx-dialog>

	</div>

</div>

<?php
/**
 * Action hook at the bottom of license page
 *
 * @action warifu_license_page_footer
 */
do_action( 'warifu_license_page_footer' );

$json = [
	'agree'        => __( 'Yes, please.', 'warifu' ),
	'disagree'     => __( 'No, keep it.', 'warifu' ),
	'may_i_remove' => __( 'Do you want remove this license?', 'warifu' ),
	'uses'         => __( 'Confirmation Count', 'warifu' ),
	'email'        => __( 'Email', 'warifu' ),
	'created_at'   => __( 'Purchased Date', 'warifu' ),
    'no_license'   => __( 'License not found', 'warifu' ),
	'confirm'      => __( 'Are you sure to remove this license?', 'warifu' ),
];
?>
<script>
	window.warifuLabels = <?php echo json_encode( $json ) ?>;
</script>

<script src="<?php echo esc_attr( warifu_asset_path( 'assets/js/lib.js' ) ) ?>?version=<?php echo WARIFU_VERSION ?>"></script>
<script src="<?php echo esc_attr( warifu_asset_path( 'assets/js/app.js' ) ) ?>?version=<?php echo WARIFU_VERSION ?>"></script>
</body>
</html>
