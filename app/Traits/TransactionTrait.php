<?php
namespace App\Traits;

use App\Models\Balance;
use App\Models\Transaction;

trait TransactionTrait {

    public function doTransaction($naam_account, $jama_account, $amount, $type, $subtype, $comment, $salary_id = null) {
        $transaction = new Transaction();
        $transaction->jama_id = $jama_account;
        $transaction->naam_id = $naam_account;
        $transaction->amount = $amount;
        $transaction->type = $type;
        $transaction->sub_type = $subtype;
        $transaction->salary_id = $salary_id;
        $transaction->comments = $comment;
        $transaction->entry_by = session('user_id');
        if ($transaction->save()) {
            $this->setBalance($jama_account, 'jama', $amount);
            $this->setBalance($naam_account, 'naam', $amount);
            return $transaction;
        }
        
        return false;
    }

    function setBalance($user_id, $type, $amount) {
        $balance = Balance::firstOrNew(array('user_id' => $user_id));
        $balance->user_id = $user_id;
        if($type == 'naam') {
            $balance->naam += $amount;
            $balance->balance -= $amount;
        } else {
            $balance->jama += $amount;
            $balance->balance += $amount;
        }
        $balance->save();
    }
}