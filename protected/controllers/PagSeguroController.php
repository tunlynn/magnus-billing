<?php

/**
 * Url for moip ruturn http://ip/billing/index.php/pagSeguro .
 * https://pagseguro.uol.com.br/preferences/automaticReturn.jhtml
 */
class PagSeguroController extends Controller
{
    public function actionIndex()
    {
        Yii::log(print_r($_POST, true), 'error');

        $filter = "payment_method = 'Pagseguro' AND t.active = 1 ";
        $params = array();

        if (isset($_GET['agent'])) {
            $filter .= " AND u.username = :username";
            $params = array(':username' => addslashes(strip_tags(trim($_GET['agent']))));
        } else {
            $filter .= " AND u.id = 1";
        }

        $modelMethodpay = Methodpay::model()->find(array(
            'condition' => $filter,
            'join'      => 'INNER JOIN pkg_user u ON t.id_user = u.id',
            'params'    => $params,
        ));

        if (!count($modelMethodpay)) {
            exit('error 30');
        }

        $email = $modelMethodpay->username;
        $TOKEN = $modelMethodpay->pagseguro_TOKEN;
        if (isset($_POST['notificationCode'])) {
            $notificationCode = $_POST['notificationCode'];

            $url  = "https://ws.pagseguro.uol.com.br/v2/transactions/notifications/" . $notificationCode . "?email=" . $email . "&token=" . $TOKEN;
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            $http     = curl_getinfo($curl);

            if ($response == 'Unauthorized') {
                Yii::log(print_r($response, true), 'error');
                exit;
            }
            curl_close($curl);
            $response = simplexml_load_string($response);

            if (count($response->error) > 0) {
                Yii::log(print_r($response, true), 'error');
                exit;
            }
            $referencia  = $response->items->item->id;
            $transacaoID = $response->code;
            $status      = $response->status;
            $amount      = number_format((float) $response->grossAmount, 2, '.', '');
            /*
            C??digo  Significado
            1   Aguardando pagamento: o comprador iniciou a transa????o, mas at?? o momento o PagSeguro n??o recebeu nenhuma informa????o sobre o pagamento.
            2   Em an??lise: o comprador optou por pagar com um cart??o de cr??dito e o PagSeguro est?? analisando o risco da transa????o.
            3   Paga: a transa????o foi paga pelo comprador e o PagSeguro j?? recebeu uma confirma????o da institui????o financeira respons??vel pelo processamento.
            4   Dispon??vel: a transa????o foi paga e chegou ao final de seu prazo de libera????o sem ter sido retornada e sem que haja nenhuma disputa aberta.
            5   Em disputa: o comprador, dentro do prazo de libera????o da transa????o, abriu uma disputa.
            6   Devolvida: o valor da transa????o foi devolvido para o comprador.
            7   Cancelada: a transa????o foi cancelada sem ter sido finalizada.
             */

            $identification = Util::getDataFromMethodPay($referencia);
            if (!is_array($identification)) {
                exit;
            }

            $username = $identification['username'];
            $id_user  = $identification['id_user'];

            if ($status == 3) {
                $description = "Pagamento confirmado, PAGSEGURO:" . $transacaoID;

                $modelUser = User::model()->findByPk((int) $id_user);

                if (count($modelUser) && Refill::model()->countRefill($transacaoID, $modelUser->id) == 0) {
                    Yii::log($modelUser->id . ' ' . $amount . ' ' . $description . ' ' . $transacaoID, 'error');
                    UserCreditManager::releaseUserCredit($modelUser->id, $amount, $description, 1, $transacaoID);
                    header("HTTP/1.1 200 OK");
                } else {
                    Yii::log(print_r('Existe uma pagamento com a referencia ' . $transacaoID, true), 'error');
                }

            } else {
                echo 'error';
            }
        } else {
            echo 'Obrigado por sua compra.';
        }

    }
}
