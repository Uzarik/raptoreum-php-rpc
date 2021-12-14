<?php

namespace Krevedko\RaptoreumPhpRpc;

use Exception;

class Wallet extends RPC
{
    /**
     * Returning account by address
     * @param string $address Raptoreum address
     */
    public function getAccountByAddress(string $address)
    {
        return $this->result('getaccount', [$address]);
    }

    /**
     * Returning address attached to account
     * @param string $address account name
     */
    public function getAddressesByAccount(string $account)
    {
        return $this->result('getaddressesbyaccount', [$account]);
    }

    /**
     * Returning balance of account
     * ! WARNING ! can return wrong value, idk why. Better use:
     * @method getRealAccountBalance()
     * @param string $address account name
     */
    public function getBalance(string $account)
    {
        return $this->result('getbalance', [$account]);
    }

    /**
     * Returning list of account in wallet
     */
    public function getListAccounts()
    {
        return $this->result('listaccounts');
    }

    /**
     * Returning list of addresses and their amount of RTM
     * @param float $minSum filtering only addresses with more then coins
     */
    public function getListAddresses(float $minSum = 0)
    {
        return $this->result('listaddressbalances', [$minSum]);
    }

    /**
     * Returning new generated address attached to account
     * @param string $account
     */
    public function getNewAddress(string $account)
    {
        return $this->result('getnewaddress', [$account]);
    }
    //0.00000236
    /**
     * ! not working. Idk why
     */
    public function moveBetweenAccounts(string $from, string $to, float $sum, string $comment = '')
    {
        return $this->result('move', [$from, $to, $sum, 0, $comment]);
    }

    public function setAccount(string $address, string $account)
    {
        return $this->result('setaccount', [$address, $account]);
    }

    /**
        sendfrom "fromaccount" "toaddress" amount ( minconf addlocked "comment" "comment_to" )
        DEPRECATED (use sendtoaddress). Sent an amount from an account to a raptoreum address.

        Arguments:
        1. "fromaccount"       (string, required) The name of the account to send funds from. May be the default account using "".
                            Specifying an account does not influence coin selection, but it does associate the newly created
                            transaction with the account, so the account's balance computation and transaction history can reflect
                            the spend.
        2. "toaddress"         (string, required) The raptoreum address to send funds to.
        3. amount              (numeric or string, required) The amount in RTM (transaction fee is added on top).
        4. minconf             (numeric, optional, default=1) Only use funds with at least this many confirmations.
        5. addlocked         (bool, optional, default=false) Whether to include transactions locked via InstantSend.
        6. "comment"           (string, optional) A comment used to store what the transaction is for. 
                            This is not part of the transaction, just kept in your wallet.
        7. "comment_to"        (string, optional) An optional comment to store the name of the person or organization 
                            to which you're sending the transaction. This is not part of the transaction, 
                            it is just kept in your wallet.

        Result:
        "txid"                 (string) The transaction id.
     */
    public function sendFrom(string $fromAccount, string $toAddress, float $amount, string $comment = '', string $commentTo = '', int $minConf = 1, bool $addLocked = false)
    {
        return $this->result('sendfrom', [$fromAccount, $toAddress, $amount, $minConf, $addLocked, $comment, $commentTo]);
    }

    /**
        sendmany "fromaccount" {"address":amount,...} ( minconf addlocked "comment" ["address",...] subtractfeefrom use_is use_ps conf_target "estimate_mode")
        Send multiple times. Amounts are double-precision floating point numbers.

        Arguments:
        1. "fromaccount"           (string, required) DEPRECATED. The account to send the funds from. Should be "" for the default account
        2. "amounts"               (string, required) A json object with addresses and amounts
        {
            "address":amount     (numeric or string) The raptoreum address is the key, the numeric amount (can be string) in RTM is the value
            ,...
        }
        3. minconf                 (numeric, optional, default=1) Only use the balance confirmed at least this many times.
        4. addlocked               (bool, optional, default=false) Whether to include transactions locked via InstantSend.
        5. "comment"               (string, optional) A comment
        6. subtractfeefrom         (array, optional) A json array with addresses.
                                The fee will be equally deducted from the amount of each selected address.
                                Those recipients will receive less raptoreums than you enter in their corresponding amount field.
                                If no addresses are specified here, the sender pays the fee.
        [
            "address"          (string) Subtract fee from this address
            ,...
        ]
        7. "use_is"                (bool, optional, default=false) Deprecated and ignored
        8. "use_ps"                (bool, optional, default=false) Use PrivateSend funds only
        9. conf_target            (numeric, optional) Confirmation target (in blocks)
        10. "estimate_mode"      (string, optional, default=UNSET) The fee estimate mode, must be one of:
            "UNSET"
            "ECONOMICAL"
            "CONSERVATIVE"

        Result:
        "txid"                   (string) The transaction id for the send. Only 1 transaction is created regardless of 
                                        the number of addresses.
     *   TODO: доделать
     */
    public function sendMany(
        string $fromAccount,
        array $amounts,
        array $subscractFeeFrom = [],
        string $comment = '',
        int $minConf = 1,
        bool $addLocked = false,
        bool $usePs = false,
        int $confTarget = 6,
        string $estimateMode = 'ECONOMICAL'
    ) {
        return $this->result('sendmany', [$fromAccount, $amounts, $minConf, $addLocked, $comment, $subscractFeeFrom, false, $usePs, $confTarget, $estimateMode]);
    }

    /**
     * Returning real list of addresses and their balances + total sum
     * ! Analog of getBalance method, but working well
     * @param string $account
     */
    public function getRealAccountBalance(string $account)
    {
        // адреса аккаунта
        $accAddresses = $this->getAddressesByAccount($account);

        // непустые адреса в кошельке
        $addresses = (array) $this->getListAddresses(0.00000001);

        $result = ['addresses' => []];
        foreach($accAddresses as $address) {
            $result['addresses'][$address] = $addresses[$address] ?? 0;
        }
        $result['total'] = array_sum($result['addresses']);

        return $result;
    }

    /**
     * Returning real list of addresses and their balances + total sum
     * ! Analog of getBalance method, but working well
     * @param string $account
     */
    public function getRealAddressBalance(string $address)
    {
        $addresses = (array) $this->getListAddresses(0.00000001);
        return $addresses[$address] ?? 0;
    }

    /**
     * Get latest $count transactions of account
     */
    public function getListTransactions(string $account = '*', int $count = 30)
    {
        return $this->result('listtransactions', [$account, $count]);
    }

    /**
     * Validating address
     */
    public function validateAddress(string $address)
    {
        return $this->result('validateaddress', [$address]);
    }

    public function getListUnspent()
    {
        return $this->result('listunspent');
    }

    public function getUnspentByAccount(string $account)
    {
        $unspent = $this->getListUnspent();
        $result = [];
        foreach($unspent as $t) {
            if($t->account == $account) {
                $result[] = $t;
            }
        }
        return $result;
    }

    public function getUnspentByAddress(string $address)
    {
        $unspent = $this->getListUnspent();
        $result = [];
        foreach($unspent as $t) {
            if($t->address == $address) {
                $result[] = $t;
            }
        }
        return $result;
    }

    /**
     * создает raw транзакцию
     */
    public function createRawTransaction(array $transactions, array $recipients)
    {
        $params1 = [];
        // unspent входы
        foreach($transactions as $transaction) {
            $params1[] = [
                'txid' => $transaction->txid,
                'vout' => $transaction->vout,
            ];
        }

        return $this->result('createrawtransaction', [$params1, $recipients]);
    }

    /**
     * подписывает raw транзакцию
     */
    public function signRawTransaction(string $tHash)
    {
        return $this->result('signrawtransaction', [$tHash]);
    }

    /**
     * отсылает raw транзакцию
     */
    public function sendRawTransaction(string $tHash)
    {
        return $this->result('sendrawtransaction', [$tHash]);
    }

    public function getTransaction(string $txId)
    {
        return $this->result('gettransaction', [$txId]);
    }

    /**
     * отправка с аккаунта на переданные адреса
     */
    public function send(string $fromAccount, array $toAddresses)
    {
        $unspent = $this->getUnspentByAccount($fromAccount);
        return $this->_send($unspent, $toAddresses);
    }

    /**
     * отправка с конкретного адреса на переданные адреса
     */
    public function sendFromAddress(string $fromAddress, array $toAddresses)
    {
        $unspent = $this->getUnspentByAddress($fromAddress);
        return $this->_send($unspent, $toAddresses);
    }

    /**
     * получение списка залоченных анспентов
     */
    public function getListLockUnspent()
    {
        return $this->result('listlockunspent');
    }

    public function lockUnspent(bool $bool, array $data)
    {
        return $this->result('lockunspent', [$bool, [$data]]);
    }

    private function _send($unspent, array $toAddresses)
    {
        $balance = array_sum(array_column($unspent, 'amount')); // баланс аккаунта
        $sum = array_sum($toAddresses); // сумма к переводу (без учета комиссии)

        if($balance < $sum) {
            throw new Exception('Недостаточно для отправки: ' . $sum . '\n' . print_r($unspent, true), 500);
        }

        /**
         * собираем из транзакций достаточную сумму для перевода
         */
        $transactions = [];
        $soaked = 0; $i = 0;
        while($soaked < $sum) {
            $transaction = $unspent[$i++];
            $transactions[] = $transaction;
            $soaked += $transaction->amount;
        }

        // TODO: разобраться с хардкодом комиссии
        $comission = 0.00000250; // дефолтная комиссия за 1 вход
        if(($count = count($transactions)) > 1) {
            $comission += ($count * 0.00000150); // комиссия за каждый дополнительный вход
        }

        if($sum + $comission > $balance) {
            // сколько не хватает
            $diff = $sum + $comission - $balance;
            // вычтем из суммы первого получателя
            $toAddresses[array_key_first($toAddresses)] -= $diff;
            // пересчитываем итоговую сумму
            $sum = array_sum($toAddresses);
        }

        /**
         * расчет сдачи
         */
        $outSum = array_sum(array_column($transactions, 'amount'));
        if($outSum > $sum + $comission) {
            $change = $outSum - $sum - $comission;
            // в качестве адреса сдачи выбираем первый адрес из "входа"
            if(!isset($toAddresses[$transactions[0]->address])) {
                $toAddresses[$transactions[0]->address] = 0;
            }
            $toAddresses[$transactions[0]->address] += $change;
        }

        // округление всех адресов, 
        foreach($toAddresses as $k => &$v) {
            $v = round($v, 8, PHP_ROUND_HALF_DOWN);
        }

        $hash = $this->createRawTransaction($transactions, $toAddresses);
        $sign = $this->signRawTransaction($hash);
        if(!$sign->complete) {
            throw new Exception('Не удалось подписать транзакцию ' . print_r($sign, true), 500);
        }
        return $this->sendRawTransaction($sign->hex);
    }
}