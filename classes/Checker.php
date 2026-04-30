<?php

namespace quizaccess_sebversion_checker;

defined('MOODLE_INTERNAL') || die();

class Checker {
//    const ALLOWED_SEB_MAC = ["3.6", "3.6.*", "3.6.*.*", "3.7", "3.7.*",
//        "3.7.*.*"
//    ];
//    const ALLOWED_SEB_MAC = ["4.0"]; // TEST
//    const ALLOWED_SEB_WIN = ["3.10", "3.10.*", "3.10.*.*", "3.11", "3.11.*",
//        "3.11.*.*"
//    ];


    private string $currentVersion = "Non rilevata";
    private bool $isSeb;
    private bool $correctVersion;
    private string $os;
    private $allowedSeb;

    private function loadCurrentVersionFromHeaders($user_agent){

        $regex = '/SEB\/([0-9.]+)/';

        // Try to get results with regex
        if (preg_match($regex, $user_agent, $matches)) {
            $this->currentVersion = $matches[1];
        }
    }

    public function isSeb(): bool
    {
        return $this->isSeb;
    }

    public function isCorrectVersion(): bool{
        return $this->correctVersion;
    }

    public function getCurrentVersion(): string
    {
        return $this->currentVersion;
    }

    public function __construct(){
        $this->loadHeaders();
    }

    private function loadHeaders(): void
    {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Check if we are in SEB
        if(isset($_SERVER['HTTP_X_SAFEEXAMBROWSER_REQUESTHASH']) || str_contains($user_agent, 'SEB')){
            $this->isSeb = true;
            $this->loadCurrentVersionFromHeaders($user_agent);
            $this->loadOSFromHeaders($user_agent);
            $this->loadListFromSettings();
            $this->checkVersionMatches();
        }
        else $this->isSeb = false;

        // ONLY FOR TEST!!!!!!!!!!
//        $this->testFakeVersion($user_agent);
    }

    /**
     * @param $user_agent
     * @return void
     */
    private function testFakeVersion($user_agent): void
    {
        $this->isSeb = true;
        $this->currentVersion = "3.6";
        $this->loadOSFromHeaders($user_agent);
        $this->loadListFromSettings();
        $this->checkVersionMatches();
    }

    private function checkVersionMatches(): void{

        $version = $this->currentVersion;
        $patterns = $this->allowedSeb;

        $this->correctVersion = false;

        // Se patterns isn't an array or empty return false
        if (!is_array($patterns) || empty($patterns)) {
            $this->correctVersion = false;
            return;
        }

        foreach ($patterns as $p) {
            // If there is not a wildcard, easy!
            if (!str_contains($p, '*')) {
                if ($p === $version) {
                    $this->correctVersion = true;
                    return;
                }
                continue;
            }

            // Transform the wildcard in Regex
            $regex = '/^' . str_replace(['.', '*'], ['\.', '\d+'], $p) . '$/';

            if (preg_match($regex, $version)) {
                $this->correctVersion = true;
                return;
            }
        }

    }

    public function getPopup(){
        if($this->correctVersion){
            $textCorrectVersion = get_string('correct_version', 'quizaccess_sebversion_checker');
            $class_alert = "alert alert-success";
        } else{
            $textCorrectVersion = get_string('wrong_version', 'quizaccess_sebversion_checker');
            $class_alert = "alert alert-danger";
        }


        return sprintf(
            '
            <div class="container-fluid mt-2" style="text-align:left;">
                <div class="%1$s" style="border-width: 3px; box-shadow: 0px 4px 8px rgba(0,0,0,0.1);">
                    <div class="d-flex align-items-center">
                        <div style="font-size: 2rem; margin-right: 15px;">🔒</div>
                        <div>
                            <h4 class="alert-heading mb-1">%2$s</h4>
                            <p class="mb-0">%3$s <strong>%4$s</strong></p>
                            <p class="mb-0"><strong>%5$s</strong></p>
                            <small class="text-muted">OS: %6$s</small>
                        </div>
                    </div>
                </div>
            </div>',
            $class_alert,
            get_string('seb_detected', 'quizaccess_sebversion_checker'),
            get_string('installed_version', 'quizaccess_sebversion_checker'),
            htmlspecialchars($this->currentVersion),
            $textCorrectVersion,
            htmlspecialchars($this->os)
        );
    }

    public function getErrorLockPopup(){

        return sprintf(
            '
            <div class="container-fluid mt-2" style="text-align:left;">
                <div class="alert alert-danger" style="border-width: 3px; box-shadow: 0px 4px 8px rgba(0,0,0,0.1);">
                    <div class="d-flex align-items-center">
                        <div style="font-size: 2rem; margin-right: 15px;">🔒</div>
                        <div>
                            <h4 class="alert-heading mb-1">%1$s</h4>
                            <p class="mb-0"><strong>%2$s</strong></p>
                        </div>
                    </div>
                </div>
            </div>',
            'ERROR',
            get_string('prevent_access_error', 'quizaccess_sebversion_checker')
        );
    }

    function loadOSFromHeaders($user_agent) {
        $ua = strtolower($user_agent);
        if (str_contains($ua, "mac os x") || str_contains($ua, "macintosh")) $this->os = "MacOS";
        else if (str_contains($ua, "windows") || str_contains($ua, "win32") || str_contains($ua, "win64")) $this->os = "Windows";
        else $this->os = "Unknown";
    }

    private function loadListFromSettings()
    {
        $pattern_config = ($this->os == "Windows") ? 'allowed_seb_win' : 'allowed_seb_mac';
        $patterns_raw = get_config('quizaccess_sebversion_checker', $pattern_config);
        $this->allowedSeb = explode("\n", str_replace("\r", "", $patterns_raw));
    }

    public function isEnabled(){
        $pattern_config = ($this->os == "Windows") ? 'enable_windows' : 'enable_mac';
        return get_config('quizaccess_sebversion_checker', $pattern_config);
    }

}