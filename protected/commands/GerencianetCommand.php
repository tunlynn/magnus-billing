<?php
/**
 * =======================================
 * ###################################
 * MagnusBilling
 *
 * @package MagnusBilling
 * @author Adilson Leffa Magnus.
 * @copyright Copyright (C) 2005 - 2021 MagnusSolution. All rights reserved.
 * ###################################
 *
 * This software is released under the terms of the GNU Lesser General Public License v2.1
 * A copy of which is available from http://www.gnu.org/copyleft/lesser.html
 *
 * Please submit bug reports, patches, etc to https://github.com/magnusbilling/mbilling/issues
 * =======================================
 * Magnusbilling.com <info@magnusbilling.com>
 *
 */

require_once Yii::app()->baseUrl . "/lib/gerencianet/vendor/autoload.php";

use Gerencianet\Exception\GerencianetException;
use Gerencianet\Gerencianet;

class GerencianetCommand extends ConsoleCommand
{

    public function run($args)
    {

        $modelMethodPay = Methodpay::model()->find('payment_method = :key', array(':key' => 'GerenciaNet'));

        $clientId     = $modelMethodPay->client_id; // insira seu Client_Id, conforme o ambiente (Des ou Prod)
        $clientSecret = $modelMethodPay->client_secret; // insira seu Client_Secret, conforme o ambiente (Des ou Prod)

        $options = [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'sandbox'       => false, // altere conforme o ambiente (true = desenvolvimento e false = producao)
        ];

        $modelRefill = Refill::model()->findAll("description LIKE '%Status:Aguardando ID:%' AND payment = 0");

        foreach ($modelRefill as $key => $refill) {

            $token = explode(" ID:", $refill->description);
            $token = $token[1];

            echo $token . "\n";
            $params = [
                'token' => $token,
            ];

            try {
                $api                = new Gerencianet($options);
                $chargeNotification = $api->getNotification($params, []);
                // Para identificar o status atual da sua transa????o voc?? dever?? contar o n??mero de situa????es contidas no array, pois a ??ltima posi????o guarda sempre o ??ltimo status. Veja na um modelo de respostas na se????o "Exemplos de respostas" abaixo.

                // Veja abaixo como acessar o ID e a String referente ao ??ltimo status da transa????o.

                // Conta o tamanho do array data (que armazena o resultado)
                $i = count($chargeNotification["data"]);
                // Pega o ??ltimo Object chargeStatus
                $ultimoStatus = $chargeNotification["data"][$i - 1];
                // Acessando o array Status
                $status = $ultimoStatus["status"];
                // Obtendo o ID da transa????o
                $charge_id = $ultimoStatus["identifiers"]["charge_id"];
                // Obtendo a String do status atual
                $statusAtual = $status["current"];

                // Com estas informa????es, voc?? poder?? consultar sua base de dados e atualizar o status da transa????o especifica, uma vez que voc?? possui o "charge_id" e a String do STATUS
                switch ($statusAtual) {
                    case 'paid':
                        echo "o boleto foi pago";
                        $description = "Boleto gerado, Status:Pago dia " . date("y-m-d") . ", ID:" . $token;
                        UserCreditManager::releaseUserCredit($refill->id_user, $refill->credit, $description, 1, $token);
                        break;
                    case 'unpaid':
                        echo "o boleto nao foi pago";
                        $description = "Boleto gerado, Status:N??o foi pago, ID:" . $token;
                        break;
                    case 'refunded':
                        echo "Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet.";
                        $description = "Boleto gerado, Status:Pagamento devolvido pelo lojista ou pelo intermediador Gerencianet, ID:" . $token;
                        break;
                    case 'contested':
                        echo "Pagamento em processo de contesta????o.";
                        $description = "Boleto gerado, Status:Pagamento em processo de contesta????o, ID:" . $token;
                        break;
                    case 'canceled':
                        echo "Cobran??a cancelada pelo vendedor ou pelo pagador.";
                        $description = "Boleto gerado, Status:Cobran??a cancelada pelo vendedor ou pelo pagador, ID:" . $token;
                        break;
                    case 'waiting':
                        echo "Cobran??a Aguardando pagamento";
                        break;
                }

                $modelRefill->description = $description;
                $modelRefill->save();

                //print_r($chargeNotification);
            } catch (GerencianetException $e) {
                print_r($e->code);
                print_r($e->error);
                print_r($e->errorDescription);
            } catch (Exception $e) {
                print_r($e->getMessage());
            }
        }
    }
}
