<?php

class Mail {

    private $request;


    public function __construct(){
        $this->request =  new \HttpClient();
    }

    public function sendMail(array  $products , array  $mailRequest , $end = false){
        $excelFile = (new \renderer\ExcelRenderer())->get($products , $mailRequest);
        if ($excelFile === false)
        {
            echo "File render error \n";
            $data = [
                'text' => "
                 Fayl hazırlananda xəta baş verdi!
                 ",
                'userid' => $mailRequest['user_id'],
            ];
            $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));
            $this->deleteMail();
            return false;
        }

        $send = \Mail::send([$excelFile] , $mailRequest['emails']);

        if ($send instanceof Exception){
            echo "mail error\n";
            $data = [
                'text' => "
                 Mail göndəriləndə xəta baş verdi!
                 Debug: {$send->ErrorInfo}",
                'userid' => $mailRequest['userid'],
            ];

            $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));

            $this->deleteMail();
            return false;
        }

        echo "Filename -> ".$excelFile . "\n";

        $emails = '';
        foreach ($mailRequest['emails'] as $email) { $emails .= "
        {$email}";
        }

        $data = [
            'text' => "
            Maillar uğurla göndərildi !
            Mail Ünvanlar:
            {$emails}
            ",
            'userid' => $mailRequest['userid'],
        ];
        $this->request->sendData("/btdn_cve/cedvel/api.php" , serialize($data));
        $this->deleteMail();
        return true;
    }
}