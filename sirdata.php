<?php
/*
Plugin Name: Sirdata CMP
Description: Manage consent frameworks and handle data processing. When a user interacts with consent prompts, their data may be sent to Sirdata for processing.
Version: 1.2.6
Author: AC WEB AGENCY
Tested up to: 6.6.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: sirdata-cmp
*/

if (!defined('ABSPATH')) {
    exit;
}

class STCMP_Sirdata
{
    protected array $content;
    protected string $language = 'en';

    public function __construct()
    {
        add_action('admin_menu', array($this, 'STCMP_add_admin_menu'));
        add_action('admin_init', array($this, 'STCMP_register_settings'));
        add_action('wp_enqueue_scripts', array($this, 'STCMP_insert_sirdata_scripts_in_header'));
        add_action('admin_notices', array($this, 'STCMP_settings_saved_notice'));
        add_action('admin_post_STCMP_register_form', array($this, 'STCMP_handle_register_form_submission'));
        add_action('admin_post_STCMP_settings_form', array($this, 'STCMP_handle_settings_form_submission'));
        add_action('admin_enqueue_scripts', array($this, 'STCMP_enqueue_admin_styles'));
        register_deactivation_hook(__FILE__, array($this, 'STCMP_deactivate'));

        if (substr(get_locale(), 0, 2) == 'fr' || substr(get_locale(), 0, 2) == 'en') {
            $this->language = substr(get_locale(), 0, 2);
        } else {
            $this->language = 'en';
        }

        $this->content = [
            'fr' => [
                'register' => [
                    'title' => 'Inscription',
                    'desc' => 'Inscrivez-vous sur notre plateforme Sirdata CMP pour utiliser notre extension. Si vous possédez déjà un compte, connectez-vous via le lien suivant pour récupérer votre Partner ID et Config ID : <a href="https://cmp.sirdata.io/cmp" target="_blank"> Connexion </a>',
                    'email' => 'Email',
                    'domain_name' => 'Nom de domaine',
                    'domain_name_format' => 'Le nom de domaine doit contenir un point (.).',
                    'STCMP_custom_consent_url' => 'URL des politiques de confidentialité',
                    'STCMP_custom_consent_accept_cgv' => 'Conditions Générales de Vente',
                    'STCMP_custom_consent_accept_cgv_checkbox' => 'J\'accepte les <a href="https://cmp.sirdata.io/terms-of-sale" target="_blank">CGV</a> de Sirdata CMP',
                    'register' => 'M\'inscrire'
                ],
                'parameters' => [
                    'title' => 'Paramètres',
                    'desc' => 'Connectez-vous via le lien suivant pour récupérer votre Partner ID et Config ID : <a href="https://cmp.sirdata.io/cmp" target="_blank"> Connexion</a>',
                    'partner_id' => 'Partner ID',
                    'config_id' => 'Config ID',
                    'save' => 'Enregistrer'
                ],
                'notices' => [
                    'settings-updated' => 'Configuration sauvegardée avec succès.',
                    'missing_id' => 'Les paramètres ont été effacés car un ou les deux champs étaient vides.',
                    'account_exists' => 'Un compte Sirdata avec cette adresse email existe déjà.',
                    'missing_parameters' => 'Tous les champs sont requis',
                    'account_created' => 'Inscription réussie. Nous vous avons envoyé un e-mail !',
                    'api_login_error' => 'Erreur de connexion à l\'API. Veuillez réessayer !',
                    'error_api' => 'Erreur : '
                ]
            ],
            'en' => [
                'register' => [
                    'title' => 'Registration',
                    'desc' => 'Sign up on our Sirdata CMP platform to use our extension. If you already have an account, connect via the following link to get your Partner ID and Config ID : <a href="https://cmp.sirdata.io/cmp" target="_blank"> Login </a>',
                    'email' => 'Email (unique ID)',
                    'domain_name' => 'Domain name (where the CMP will be deployed)',
                    'domain_name_format' => 'The domain name must contain a dot (.).',
                    'STCMP_custom_consent_url' => 'Privacy policy URL',
                    'STCMP_custom_consent_accept_cgv' => 'General Terms of Sale',
                    'STCMP_custom_consent_accept_cgv_checkbox' => "I accept the <a href=\"https://cmp.sirdata.io/terms-of-sale\" target=\"_blank\">general terms of sale</a>",
                    'register' => 'Register'
                ],
                'parameters' => [
                    'title' => 'Settings',
                    'desc' => 'Log in via the following link to retrieve your Partner ID and Config ID: <a href="https://cmp.sirdata.io/cmp" target="_blank"> Login</a>',
                    'partner_id' => 'Partner ID',
                    'config_id' => 'Config ID',
                    'save' => 'Save changes'
                ],
                'notices' => [
                    'settings-updated' => 'Configuration successfully saved.',
                    'missing_id' => 'Settings have been cleared because one or both fields were empty.',
                    'account_exists' => 'A Sirdata account with this email address already exists.',
                    'missing_parameters' => 'All fields are required',
                    'account_created' => 'Successful registration. We\'ve sent you an email !',
                    'api_login_error' => 'API connection error. Please try again !',
                    'error_api' => 'Error : '
                ]
            ]
        ];
    }


    public function STCMP_deactivate()
    {
        delete_option('STCMP_custom_consent_partner_id');
        delete_option('STCMP_custom_consent_config_id');
        delete_option('STCMP_custom_consent_email');
        delete_option('STCMP_custom_consent_domain');
        delete_option('STCMP_custom_consent_url');
        delete_option('STCMP_custom_consent_accept_cgv');
    }

    public function STCMP_add_admin_menu()
    {
        add_menu_page('Sirdata', 'Sirdata CMP', 'manage_options', 'custom-consent-scripts', array($this, 'STCMP_settings_page'));
    }

    public function STCMP_register_settings()
    {
        register_setting('custom-consent-scripts', 'STCMP_custom_consent_partner_id');
        register_setting('custom-consent-scripts', 'STCMP_custom_consent_config_id');
        register_setting('custom-consent-scripts', 'STCMP_custom_consent_email');
        register_setting('custom-consent-scripts', 'STCMP_custom_consent_domain');
        register_setting('custom-consent-scripts', 'STCMP_custom_consent_url');
        register_setting('custom-consent-scripts', 'STCMP_custom_consent_accept_cgv');
    }

    public function STCMP_settings_page()
    {
?>

        <div class="wrap">
            <h2>
                <img src="<?php echo esc_url(plugin_dir_url(__FILE__)) . 'sirdata.png'; ?>" alt="Company Logo"
                    style="width: 150px; height: auto;">
            </h2>

            <div style="display: flex;">
                <div style="flex: 1; margin-right: 20px; margin-top: 10px !important;" class="column left">
                    <h2><?php echo esc_html($this->content[$this->language]['register']['title']); ?></h2>
                    <p><?php echo $this->content[$this->language]['register']['desc']; ?></p>
                    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                        <input type="hidden" name="action" value="STCMP_register_form">
                        <?php wp_nonce_field('STCMP_register_form_action', 'STCMP_register_form_nonce'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row"><?php echo esc_html($this->content[$this->language]['register']['email']); ?>
                                    <span style="color:red">*</span>
                                </th>
                                <td><input type="email" name="STCMP_custom_consent_email"
                                        value="<?php echo esc_attr(get_option('STCMP_custom_consent_email')); ?>" required />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <?php echo esc_html($this->content[$this->language]['register']['domain_name']); ?> <span
                                        style="color:red">*</span></th>
                                <td><input type="text" name="STCMP_custom_consent_domain"
                                        value="<?php echo esc_attr(get_option('STCMP_custom_consent_domain')); ?>" required
                                        pattern=".*\..*"
                                        title="<?php echo esc_attr($this->content[$this->language]['register']['domain_name_format']); ?>" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <?php echo esc_html($this->content[$this->language]['register']['STCMP_custom_consent_url']); ?>
                                    <span style="color:red">*</span>
                                </th>
                                <td><input type="url" name="STCMP_custom_consent_url"
                                        value="<?php echo esc_attr(get_option('STCMP_custom_consent_url')); ?>" required /></td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                </th>
                                <td>
                                    <input type="checkbox" name="STCMP_custom_consent_accept_cgv" value="1"
                                        <?php checked(1, get_option('STCMP_custom_consent_accept_cgv'), true); ?> required />
                                    <label
                                        for="STCMP_custom_consent_accept_cgv"><?php echo $this->content[$this->language]['register']['STCMP_custom_consent_accept_cgv_checkbox']; ?></label>
                                </td>
                            </tr>
                        </table>
                        <div style="text-align: right;">
                            <?php submit_button(esc_html($this->content[$this->language]['register']['register'])); ?>
                        </div>
                    </form>
                </div>
                <div style="flex: 1;  margin-top: 10px !important;" class="column right">
                    <h2><?php echo esc_html($this->content[$this->language]['parameters']['title']); ?></h2>
                    <p><?php echo $this->content[$this->language]['parameters']['desc']; ?></p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <input type="hidden" name="action" value="STCMP_settings_form">
                        <?php wp_nonce_field('STCMP_settings_form_action', 'STCMP_settings_form_nonce'); ?>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">
                                    <?php echo esc_html($this->content[$this->language]['parameters']['partner_id']); ?></th>
                                <td><input type="text" name="STCMP_custom_consent_partner_id"
                                        value="<?php echo esc_attr(get_option('STCMP_custom_consent_partner_id')); ?>" />
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">
                                    <?php echo esc_html($this->content[$this->language]['parameters']['config_id']); ?></th>
                                <td><input type="text" name="STCMP_custom_consent_config_id"
                                        value="<?php echo esc_attr(get_option('STCMP_custom_consent_config_id')); ?>" /></td>
                            </tr>
                        </table>
                        <div style="text-align: right;">
                            <?php submit_button(esc_html($this->content[$this->language]['parameters']['save'])); ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
<?php
    }



    public function STCMP_insert_sirdata_scripts_in_header()
    {
        $partner_id = esc_js(get_option('STCMP_custom_consent_partner_id'));
        $config_id = esc_js(get_option('STCMP_custom_consent_config_id'));
        if (!empty($partner_id) && !empty($config_id) && (get_option('STCMP_custom_consent_config_id') !== '/') && get_option('STCMP_custom_consent_partner_id') !== '/') {

            // Enqueue the stub script
            wp_enqueue_script(
                'sirdata-stub-script',
                "https://cache.consentframework.com/js/pa/$partner_id/c/$config_id/stub",
                array(),
                null,
                false // false means the script will be included in the head
            );

            // Enqueue the cmp script
            wp_enqueue_script(
                'sirdata-cmp-script',
                "https://choices.consentframework.com/js/pa/$partner_id/c/$config_id/cmp",
                array(),
                null,
                false // false means the script will be included in the head
            );
        } else {
            delete_option('STCMP_custom_consent_partner_id');
            delete_option('STCMP_custom_consent_config_id');
        }
    }

    public function STCMP_settings_saved_notice()
    {
        if (!isset($_GET['form'])) {
            return;
        }

        $form = sanitize_text_field(wp_unslash($_GET['form']));

        if ($form === 'register') {
            if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($this->content[$this->language]['notices']['settings-updated']) . '</p></div>';
            } elseif (isset($_GET['account_exists'])) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($this->content[$this->language]['notices']['account_exists']) . '</p></div>';
            } elseif (isset($_GET['account_created'])) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($this->content[$this->language]['notices']['account_created']) . '</p></div>';
            } elseif (isset($_GET['missing_parameters'])) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($this->content[$this->language]['notices']['missing_parameters']) . '</p></div>';
            } elseif (isset($_GET['error_api'])) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($this->content[$this->language]['notices']['error_api']) . ' ' . esc_html(get_option('STCMP_error_api')) . '</p></div>';
            }
        } elseif ($form === 'settings') {
            if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($this->content[$this->language]['notices']['settings-updated']) . '</p></div>';
            } elseif (isset($_GET['missing_id'])) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($this->content[$this->language]['notices']['missing_id']) . '</p></div>';
            }
        }
    }


    public function STCMP_handle_settings_form_submission()
    {
        if (!isset($_POST['STCMP_settings_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['STCMP_settings_form_nonce'])), 'STCMP_settings_form_action')) {
            wp_die(esc_html__('Security check failed', 'sirdata-cmp'));
        }

        $partner_id = sanitize_text_field($_POST['STCMP_custom_consent_partner_id']);
        $config_id = sanitize_text_field($_POST['STCMP_custom_consent_config_id']);

        update_option('STCMP_custom_consent_partner_id', $partner_id);
        update_option('STCMP_custom_consent_config_id', $config_id);
        if (empty($_POST['STCMP_custom_consent_partner_id']) || empty($_POST['STCMP_custom_consent_config_id'])) {
            $data_notices = 'missing_id=true';
            delete_option('STCMP_custom_consent_partner_id');
            delete_option('STCMP_custom_consent_config_id');
            wp_redirect(admin_url('admin.php?page=custom-consent-scripts&' . $data_notices . '&form=settings'));
            exit;
        }
        $data_notices = 'settings-updated=true';
        wp_redirect(admin_url('admin.php?page=custom-consent-scripts&' . $data_notices . '&form=settings'));
        exit;
    }


    public function STCMP_handle_register_form_submission()
    {
        if (!isset($_POST['STCMP_register_form_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['STCMP_register_form_nonce'])), 'STCMP_register_form_action')) {
            wp_die(esc_html__('Security check failed', 'sirdata-cmp'));
        }

        $email = isset($_POST['STCMP_custom_consent_email']) ? sanitize_email(wp_unslash($_POST['STCMP_custom_consent_email'])) : '';
        $domain = isset($_POST['STCMP_custom_consent_domain']) ? sanitize_text_field(wp_unslash($_POST['STCMP_custom_consent_domain'])) : '';
        $url = isset($_POST['STCMP_custom_consent_url']) ? esc_url_raw($_POST['STCMP_custom_consent_url']) : '';
        $terms_accepted = isset($_POST['STCMP_custom_consent_accept_cgv']) ? true : false;

        if (empty($email) || empty($domain) || empty($url)) {
            $data_notices = 'missing_parameters=true';
            wp_redirect(admin_url('admin.php?page=custom-consent-scripts&' . $data_notices . '&form=register'));
            exit;
        }

        $data = array(
            'email' => $email,
            'domain' => $domain,
            'privacy_policy_url' => $url,
            'logo_url' => '',
            'terms_of_sale_accepted' => $terms_accepted,
            'origin' => 'wordpress'
        );

        $response = wp_remote_post('https://gateway.sirdata.io/api/v1/public/cmp-api/external/register', array(
            'method'    => 'POST',
            'body'      => wp_json_encode($data),
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('API Error: ' . $error_message);
            $data_notices = 'api_login_error=true';
        } else {
            $response_body = wp_remote_retrieve_body($response);
            $response_code = json_decode($response_body)->code;
            $response = json_decode($response_body);
            if (isset($response->partner_id) && isset($response->cmp_config_id)) {
                $data_notices = 'account_created=true';
            }
            if ($response_code == "account_exists") {
                $data_notices = 'account_exists=true';
            } elseif (isset($response_code)) {
                $data_notices = 'error_api=true';
                update_option('STCMP_error_api', $response->message);
                error_log('API Response: ' . $response_body);
            }
        }

        // Save the options to the database
        update_option('STCMP_custom_consent_email', $email);
        update_option('STCMP_custom_consent_domain', $domain);
        update_option('STCMP_custom_consent_url', $url);
        update_option('STCMP_custom_consent_accept_cgv', $terms_accepted ? 1 : 0);

        // Redirect back to the settings page
        wp_redirect(admin_url('admin.php?page=custom-consent-scripts&' . $data_notices . '&form=register'));
        exit;
    }

    public function STCMP_enqueue_admin_styles($hook_suffix)
    {
        $plugin_url = esc_url(plugin_dir_url(__FILE__));

        if ($hook_suffix == 'toplevel_page_custom-consent-scripts') {
            wp_enqueue_style('sirdata_admin_css',  $plugin_url . 'custom.css');
        }
    }
}

new STCMP_Sirdata();
?>
