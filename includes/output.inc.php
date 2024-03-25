<?php

class Output {
    public string|array|null $Result = "";
    public eState $State = eState::Success;
    public eStatusCode $StatusCode = eStatusCode::Ok;
    public Throwable|string|null $Error = null;
    public array|null $Payload = null;


    public function __construct(string|array|null $result, eState $state = eState::Success, eStatusCode $statusCode = eStatusCode::Ok, Throwable|string|null $error = null, array|null $payload = null) {
        $this->Result = $result;
        $this->State = $state;
        $this->StatusCode = $statusCode;
        $this->Error = $error;
        $this->Payload = $payload;
    }


    /**
     * Die Informationen aus diesem Output werden als JSON ausgegeben (echo)
     * 
     * @param   ?eStatusCode    $statusCode     Der Statuscode im HTTP Header. Wird keiner angegeben, wird der aus dem Property verwendet.
     */
    public function send(?eStatusCode $statusCode = null){
        if(!is_null($statusCode)){
            $this->StatusCode = $statusCode;
        }

        $output = [
            "status" => $this->State
        ];
        if(!is_null($this->Result) && $this->Result !== ''){
            $output["result"] = $this->Result;
        }
        if(!is_null($this->Error)){
            if($this->Error instanceof Throwable){
                $output["error"] = $this->Error->getMessage();
            }else{
                $output["error"] = $this->Error;
            }
        }

        $payload = $this->Payload;
        if(!is_null($payload)){
            if(isset($payload['result'])){
                $payload['_result'] = $payload['result'];
                unset($payload['result']);
            }
            if(isset($payload['error'])){
                $payload['_error'] = $payload['error'];
                unset($payload['error']);
            }
            if(isset($payload['status'])){
                $payload['_status'] = $payload['status'];
                unset($payload['status']);
            }
            $output = array_merge($output, $payload);
        }

        header('Content-Type: application/json');
        header($this->StatusCode->value);
        // echo json_encode($output);
        echo json_encode($output, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
    }


    /**
     * Es werden die übergebenen Daten als JSON mit echo ausgegeben.
     * 
     * @param   string|array|Throwable|null $result     Abhängig von der Eingabe wird die Ausgabe angepasst.
     *                                      Wird ein Execution angegeben, wird Staus zu einem Fehler. Die Error-Message wird als `error` ausgegeben. Es wird kein `result` ausgegeben.
     *                                      Wird ein Text/array angegeben, wird dieser als `result` ausgegeben, ausser $state ist Error, dann wird kein `result` sondern der `error`ausgegeben.
     * @param   eState      $state          Status, kann Success, Error oder Warning sein.
     * @param   eStatusCode $eStatusCode    Gibt an, welche HTTP-Header StatusCode ausgegben wird.
     * @param   ?array      $payload        Es können noch weitere Ausgaben bei deer Ausgabe gemacht werdne. Diese müssen als Array angegeben werden. Diese werden direkt auf selber Ebene wie `result` oder `status` ausgegeben.
     */
    static function sendNow(string|array|Throwable|null $result, eState $state = eState::Unknown, eStatusCode $statusCode = eStatusCode::Unknown, array|null $payload = null){
        $error = null;
        if($result instanceof Throwable){

    echo "hier?";
            $error = $result->getMessage();
    echo $error;
            $result = null;
            if($state == eState::Unknown){
                $state = eState::Error;
            }
            if($statusCode == eStatusCode::Unknown){
                $statusCode == eStatusCode::Error;
            }
        }
        if($state == eState::Error){
            $error = $result;
            $result = null;
        }

        if($state == eState::Unknown){
            $state = eState::Success;
        }
        if($statusCode == eStatusCode::Unknown){
            $statusCode == eStatusCode::Ok;
        }
        $output = new Output($result, $state, $statusCode, $error, $payload);
        $output->send($statusCode);
    }

}
