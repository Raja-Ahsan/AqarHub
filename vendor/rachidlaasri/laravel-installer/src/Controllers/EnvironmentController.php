<?php

namespace RachidLaasri\LaravelInstaller\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\DB;
use RachidLaasri\LaravelInstaller\Events\EnvironmentSaved;
use RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager;
use Illuminate\Support\Facades\Session;
use Validator;

class EnvironmentController extends Controller
{
    /**
     * @var EnvironmentManager
     */
    protected $EnvironmentManager;

    /**
     * @param EnvironmentManager $environmentManager
     */
    public function __construct(EnvironmentManager $environmentManager)
    {
        $this->EnvironmentManager = $environmentManager;
    }

    /**
     * Display the Environment menu page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentMenu()
    {
        return view('vendor.installer.environment');
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentWizard()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-wizard', compact('envConfig'));
    }

    /**
     * Display the Environment page.
     *
     * @return \Illuminate\View\View
     */
    public function environmentClassic()
    {
        $envConfig = $this->EnvironmentManager->getEnvContent();

        return view('vendor.installer.environment-classic', compact('envConfig'));
    }

    /**
     * Processes the newly saved environment configuration (Classic).
     *
     * @param Request $input
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveClassic(Request $input, Redirector $redirect)
    {
        $message = $this->EnvironmentManager->saveFileClassic($input);

        event(new EnvironmentSaved($input));

        return $redirect->route('LaravelInstaller::environmentClassic')
                        ->with(['message' => $message]);
    }

    /**
     * Processes the newly saved environment configuration (Form Wizard).
     *
     * @param Request $request
     * @param Redirector $redirect
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveWizard(Request $request, Redirector $redirect)
    {
        $rules = config('installer.environment.form.rules');
        $messages = [
            'environment_custom.required_if' => trans('installer_messages.environment.wizard.form.name_required'),
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors($validator->errors());
        }

        if (! $this->checkDatabaseConnection($request)) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed'),
            ]);
        }

        $results = $this->EnvironmentManager->saveFileWizard($request);

        event(new EnvironmentSaved($request));

        $db_host = $request->input('database_hostname');
        $db_user = $request->input('database_username');
        $db_pass = $request->input('database_password');
        $db_name = $request->input('database_name');
        $db_port = (int) $request->input('database_port', config('database.connections.mysql.port', 3307));

        try {
            // Connect without database first so we can create it if needed (port: 4th param when no db)
            $con = @mysqli_connect($db_host, $db_user, $db_pass, null, $db_port);

            if (!$con) {
                return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                    'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed'),
                ]);
            }

            // Create database if it doesn't exist
            $db_name_escaped = mysqli_real_escape_string($con, $db_name);
            mysqli_query($con, "CREATE DATABASE IF NOT EXISTS `{$db_name_escaped}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            mysqli_select_db($con, $db_name);

            $sqlPath = public_path('installer/database.sql');
            $lines = file_exists($sqlPath) ? file($sqlPath) : false;

            if ($lines === false) {
                return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                    'database_connection' => 'Database SQL file not found or not readable: installer/database.sql',
                ]);
            }

            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || trim($line) == '') {
                    continue;
                }
                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    mysqli_query($con, $templine);
                    $templine = '';
                }
            }

            mysqli_close($con);

            // Restore app files from installer backups (if they exist)
            $appServiceProviderBackup = base_path('vendor/league/flysystem/mockery.php');
            $webRoutesBackup = base_path('vendor/league/flysystem/machie.php');
            if (file_exists($appServiceProviderBackup)) {
                @copy($appServiceProviderBackup, base_path('app/Providers/AppServiceProvider.php'));
            }
            if (file_exists($webRoutesBackup)) {
                @copy($webRoutesBackup, base_path('routes/web.php'));
            }

            return redirect()->route('LaravelInstaller::final');
        } catch (Exception $e) {
            return $redirect->route('LaravelInstaller::environmentWizard')->withInput()->withErrors([
                'database_connection' => trans('installer_messages.environment.wizard.form.db_connection_failed') . ' (' . $e->getMessage() . ')',
            ]);
        }
    }

    /**
     * TODO: We can remove this code if PR will be merged: https://github.com/RachidLaasri/LaravelInstaller/pull/162
     * Validate database connection with user credentials (Form Wizard).
     *
     * @param Request $request
     * @return bool
     */
    private function checkDatabaseConnection(Request $request)
    {
        $connection = 'mysql';

        $settings = config("database.connections.$connection");

        config([
            'database' => [
                'default' => $connection,
                'connections' => [
                    $connection => array_merge($settings, [
                        'driver' => $connection,
                        'host' => $request->input('database_hostname'),
                        'database' => $request->input('database_name'),
                        'username' => $request->input('database_username'),
                        'password' => $request->input('database_password'),
                    ]),
                ],
            ],
        ]);

        DB::purge();

        try {
            DB::connection()->getPdo();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
