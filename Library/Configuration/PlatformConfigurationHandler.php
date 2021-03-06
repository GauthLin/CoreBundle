<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *A
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Configuration;

use \RuntimeException;
use Symfony\Component\Yaml\Yaml;
use Claroline\CoreBundle\Entity\Workspace\Workspace;
use Claroline\CoreBundle\Library\Configuration\PlatformConfiguration;
use JMS\DiExtraBundle\Annotation as DI;

/**
 * @DI\Service("claroline.config.platform_config_handler")
 *
 * Service used for accessing or modifying the platform configuration parameters.
 */
class PlatformConfigurationHandler
{
    private $configFile;
    private $parameters;
    private $defaultParameters = array(
        'name' => null,
        'nameActive' => true,
        'support_email' => null,
        'footer' => null,
        'logo' => 'clarolineconnect.png',
        'allow_self_registration' => true,
        'locale_language' => 'fr',
        'theme' => 'claroline',
        'default_role' => 'ROLE_USER',
        'cookie_lifetime' => 3600,
        'mailer_transport' => 'smtp',
        'mailer_host' => null,
        'mailer_port' => null,
        'mailer_encryption' => null,
        'mailer_username' => null,
        'mailer_password' => null,
        'mailer_auth_mode' => null,
        'terms_of_service' => true,
        'google_meta_tag' => null,
        'redirect_after_login' => false,
        'session_storage_type' => 'native',
        'session_db_table' => null,
        'session_db_id_col' => null,
        'session_db_data_col' => null,
        'session_db_time_col' => null,
        'session_db_dsn' => null,
        'session_db_user' => null,
        'session_db_password' => null,
        'form_captcha' => true,
        'platform_limit_date' => 1559350861,//1 june 2019
        'platform_init_date' => 1388534461, //1 june 2014
        'account_duration' => null,
        'username_regex' => '/^[a-zA-Z0-9@\-_\.]*$/',
        'anonymous_public_profile' => false,
        'home_menu' => null,
        'footer_login' => false,
        'footer_workspaces' => false,
        'header_locale' => false,
        'portfolio_url' => null,
        'is_notification_active' => true,
        'max_storage_size' => Workspace::DEFAULT_MAX_STORAGE_SIZE,
        'max_upload_resources' => Workspace::DEFAULT_MAX_FILE_COUNT,
        'max_workspace_users' => Workspace::DEFAULT_MAX_USERS,
        'confirm_send_datas' => null,
        'token' => null,
        'country' => '-',
        'datas_sending_url' => 'http://stats.claroline.net/insert.php',
        'repository_api' => 'http://packages.claroline.net/api.php',
        'auto_logging_after_registration' => false,
        'registration_mail_validation' => false,
        'resource_soft_delete' => false
    );
    private $lockedParameters;

    /**
     * @DI\InjectParams({
     *     "configFile"       = @DI\Inject("%claroline.param.platform_options_file%"),
     *     "lockedConfigFile" = @DI\Inject("%claroline.param.locked_platform_options_file%")
     * })
     */
    public function __construct($configFile, $lockedConfigFile)
    {
        $this->configFile = $configFile;
        $this->parameters = $this->mergeParameters();
        $this->lockedParameters = $this->generateLockedParameters($lockedConfigFile);
    }

    public function hasParameter($parameter)
    {
        if (array_key_exists($parameter, $this->parameters)) {
            return true;
        }

        return false;
    }

    public function getParameter($parameter)
    {
        if ($this->hasParameter($parameter)) return $this->parameters[$parameter];
    }

    public function setParameter($parameter, $value)
    {
        if (!is_writable($this->configFile)) {
            $exception = new UnwritableException();
            $exception->setPath($this->configFile);

            throw $exception;
        }

        $this->parameters[$parameter] = $value;
        $this->saveParameters();
    }

    public function setParameters(array $parameters)
    {
        $toMerge = array();

        foreach ($parameters as $key => $value) {

            if (!isset($this->lockedParameters[$key])) {
                $toMerge[$key] = $value;
            }
        }
        $this->parameters = array_merge($this->parameters, $toMerge);
        $this->saveParameters();
    }

    public function getPlatformConfig()
    {
        $config = new PlatformConfiguration();
        $config->setName($this->parameters['name']);
        $config->setNameActive($this->parameters['nameActive']);
        $config->setSupportEmail($this->parameters['support_email']);
        $config->setFooter($this->parameters['footer']);
        $config->setSelfRegistration($this->parameters['allow_self_registration']);
        $config->setLocaleLanguage($this->parameters['locale_language']);
        $config->setTheme($this->parameters['theme']);
        $config->setDefaultRole($this->parameters['default_role']);
        $config->setTermsOfService($this->parameters['terms_of_service']);
        $config->setCookieLifetime($this->parameters['cookie_lifetime']);
        $config->setMailerTransport($this->parameters['mailer_transport']);
        $config->setMailerHost($this->parameters['mailer_host']);
        $config->setMailerEncryption($this->parameters['mailer_encryption']);
        $config->setMailerUsername($this->parameters['mailer_username']);
        $config->setMailerPassword($this->parameters['mailer_password']);
        $config->setMailerAuthMode($this->parameters['mailer_auth_mode']);
        $config->setMailerPort($this->parameters['mailer_port']);
        $config->setGoogleMetaTag($this->parameters['google_meta_tag']);
        $config->setRedirectAfterLogin($this->parameters['redirect_after_login']);
        $config->setSessionStorageType($this->parameters['session_storage_type']);
        $config->setSessionDbTable($this->parameters['session_db_table']);
        $config->setSessionDbIdCol($this->parameters['session_db_id_col']);
        $config->setSessionDbDataCol($this->parameters['session_db_data_col']);
        $config->setSessionDbTimeCol($this->parameters['session_db_time_col']);
        $config->setSessionDbDsn($this->parameters['session_db_dsn']);
        $config->setSessionDbUser($this->parameters['session_db_user']);
        $config->setSessionDbPassword($this->parameters['session_db_password']);
        $config->setFormCaptcha($this->parameters['form_captcha']);
        $config->setAccountDuration($this->parameters['account_duration']); //days
        $config->setPlatformLimitDate($this->parameters['platform_limit_date']);
        $config->setPlatformInitDate($this->parameters['platform_init_date']);
        $config->setUsernameRegex($this->parameters['username_regex']);
        $config->setAnonymousPublicProfile($this->parameters['anonymous_public_profile']);
        $config->setHomeMenu($this->parameters['home_menu']);
        $config->setFooterLogin($this->parameters['footer_login']);
        $config->setFooterWorkspaces($this->parameters['footer_workspaces']);
        $config->setHeaderLocale($this->parameters['header_locale']);
        $config->setPortfolioUrl($this->parameters['portfolio_url']);
        $config->setIsNotificationActive($this->parameters['is_notification_active']);
        $config->setMaxUploadResources($this->parameters['max_upload_resources']);
        $config->setMaxStorageSize($this->parameters['max_storage_size']);
        $config->setRepositoryApi($this->parameters['repository_api']);
        $config->setWorkspaceMaxUsers($this->parameters['max_workspace_users']);
        $config->setAutoLogginAfterRegistration($this->parameters['auto_logging_after_registration']);
        $config->setRegistrationMailValidation($this->parameters['registration_mail_validation']);

        return $config;
    }

    protected function mergeParameters()
    {
        if (!file_exists($this->configFile) && false === @touch($this->configFile)) {
            throw new \Exception(
                "Configuration file '{$this->configFile}' does not exits and cannot be created"
            );
        }

        $configParameters = Yaml::parse(file_get_contents($this->configFile)) ?: array();
        $parameters = $this->defaultParameters;

        foreach ($configParameters as $parameter => $value) {
            $parameters[$parameter] = $value;
        }

        return $parameters;
    }

    protected function saveParameters()
    {
        file_put_contents($this->configFile, Yaml::dump($this->parameters));
    }

    protected function checkParameter($parameter)
    {
        if (!$this->hasParameter($parameter)) {
            throw new RuntimeException(
                "'{$parameter}' is not a parameter of the current platform configuration."
            );
        }
    }

    public function getDefaultParameters()
    {
        return $this->defaultParameters;
    }

    public function getLockedParamaters()
    {
        return $this->lockedParameters;
    }

    protected function generateLockedParameters($lockedConfigFile)
    {
        $lockedParameters = array();

        if (file_exists($lockedConfigFile)) {
            $lockedConfigParameters = Yaml::parse(file_get_contents($lockedConfigFile)) ?: array();

            foreach ($lockedConfigParameters as $parameter => $value) {
                $lockedParameters[$parameter] = $value;
            }
        }

        return $lockedParameters;
    }
}
