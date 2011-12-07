<?php
abstract class AVSAP_AVSAPScore {

    public function setType($class, $subassessment = NULL) {
        switch($class) {
            case(AVSAP_CLASS_INSTITUTION):
                $this->XMLFile = "institution.xml";
                break;
            case(AVSAP_CLASS_STORAGEFACILITY):
                $this->XMLFile = "storagefacility.xml";
                break;
            case(AVSAP_CLASS_ASSESSMENT):
                switch($subassessment) {
                    case(AVSAP_GENERAL):
                        $this->XMLFile = "assessment_general.xml";
                        break;
                    case(AVSAP_FILM):
                        $this->XMLFile = "assessment_film.xml";
                        break;
                    case(AVSAP_ACASSETTE):
                        $this->XMLFile = "assessment_audiocassette.xml";
                        break;
                    case(AVSAP_VCASSETTE):
                        $this->XMLFile = "assessment_videocassette.xml";
                        break;
                    case(AVSAP_VOPENREEL):
                        $this->XMLFile = "assessment_openreelvideo.xml";
                        break;
                    case(AVSAP_AOPENREEL):
                        $this->XMLFile = "assessment_openreelaudio.xml";
                        break;
                    case(AVSAP_OPTICAL):
                        $this->XMLFile = "assessment_opticalmedia.xml";
                        break;
                    case(AVSAP_WIREAUDIO):
                        $this->XMLFile = "assessment_wireaudio.xml";
                        break;
                    case(AVSAP_GROOVEDDISC):
                        $this->XMLFile = "assessment_grooveddisc.xml";
                        break;
                    case(AVSAP_GROOVEDCYL):
                        $this->XMLFile = "assessment_groovedcylinder.xml";
                        break;
                }
            }
        }

        public function loadCoefficients() {
            $file = "packages/avsap/lib/weights/{$this->XMLFile}";
            $strXML = file_get_contents($file);
            $objXML = simplexml_load_string($strXML);
            foreach (get_object_vars($objXML) as $name => $value) {
                $this->Coefficients[$name] = $value;
            }
        }


        public function calculateScore($objAVSAP) {
            $score = 0.0;
            
            $variables = get_object_vars($objAVSAP);
            foreach ($variables as $name => $value) {
                if(isset($this->Coefficients[$name])){
                    $score += $this->Coefficients[$name] * $value;
                } else if ($name == "SubAssessment") {
                    foreach ($value as $subname => $subvalue) {
                        if(isset($this->Coefficients[$subname])){
                            $score += $this->Coefficients[$subname] * $subvalue;
                        }
                    }
                }
            }
            return $score;
        }


        public function getTotalWeight() {
            $weight = 0.0;
            foreach($this->Coefficients as $var => $val) {
                $weight += $val;
            }

            if(!$weight) {
                $weight = 1.0;
            }
            
            return $weight;
        }


        public $XMLFile = '';

        public $Coefficients = array();
    }

    $_ARCHON->mixClasses('AVSAPScore', 'AVSAP_AVSAPScore');
    
    ?>
