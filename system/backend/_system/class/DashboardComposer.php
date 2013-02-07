<?php

    namespace backend;
    
    use backend\Frontend;
    use backend\Util;
    use vendor\Symfony\Component\Yaml\Yaml;

    class DashboardComposer {
        
        private $dashboard = Array();
        private $dashboardData = Array();
        private $dashboardConfig = Array();
        
        
        public function viewDashboard($dashFilename, $templateHtml){
            global $_M_THIS_CONFIG;
            global $_M_MENU;
            global $_M_MENU_PARTS;
            global $_M_MENU_MODULE;
            global $_M_USER;
            
            // IMPORTA E RODA A CLASSE DE DASHBOARD E CONFIGURAÇÕES
            if(
                file_exists($filenameDash = "dashboards/$dashFilename.php") &&
                file_exists($filenameYaml = "dashboards/$dashFilename.yml")
            ){
                // calsse do dashboard
                require_once $filenameDash;
                $this->dashboard = new \Dashboard();
                $this->dashboardData = $this->dashboard->getData();
                
                // arquivo de configuração
                $this->dashboardConfig = Yaml::parse(file_get_contents($filenameYaml));
            }else{
                echo "Um dos arquivos de Dashboard não foram encontrado '$filenameDash' ou '$filenameYaml'.";
                exit;
            }
            // Array To Layout
            $arrayBase = Array(  
                "user" => $_M_USER ,
                "main-menu" => $_M_MENU ,
                "main-menu-parts" => $_M_MENU_PARTS,
                "menu-modules" => $_M_MENU_MODULE,
                "dashboard" => $dashFilename
            );
            
            // MODULE FOLDER
            $moduleFolder = preg_replace("/(.*)\/.*$/i", "$1", $dashFilename);
            
            // JS HEAD
            $jsHead = Array();
            foreach($this->dashboardConfig['interface']['javascript']['head'] as $file){
                $jsHead[] = "dashboards/$moduleFolder/$file";
            }
            
            // JS BODY
            $jsBody = Array();
            foreach($this->dashboardConfig['interface']['javascript']['body'] as $file){
                $jsBody[] = (string)new Frontend("dashboards/$moduleFolder/$file", $arrayBase);
            }
            
            // CSS HEAD
            $styles = Array();
            foreach($this->dashboardConfig['interface']['css'] as $file){
                $styles[] = "dashboards/$moduleFolder/$file";
            }
            
            // 
            $arrayBase["css"] = $styles;
            $arrayBase["javascript"]["head"] = $jsHead;
            $arrayBase["javascript"]["body"] = $jsBody;
            
            // GERA O ARRAY PARA LAYOUT
            $toLayout = array_merge(
                    $_M_THIS_CONFIG,
                    $arrayBase,
                    $this->dashboardData
                )
            ;
            
            // 
            if( file_exists($fileHtml = "dashboards/$moduleFolder/{$this->dashboardConfig['interface']['html']}" ) ){
                $toLayout["dashboardHtml"] = (string)new Frontend($fileHtml, $toLayout);
            }else{
                 $toLayout["dashboardHtml"] = "'$fileHtml' não foi encontrado.";
            }
            
            // GERA O HTML
            echo new Frontend($_M_THIS_CONFIG['template'] ."/". $templateHtml, $toLayout);
        }
        
    }