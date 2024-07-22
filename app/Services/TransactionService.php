<?php

namespace App\Services;
;
use App\Models\Balance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Transaction as ATrans;

class TransactionService
{
    const ITEM_PER_PAGE = 10;

    public function list(Request $request)
    {
        $searchParams = $request->all();
        $date = $request->get('daterange');
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $transactions = ATrans::with([
            'jama_account', 'naam_account'
        ])
            ->when($date[0], function ($query) use ($date) {
                $date_from = Carbon::parse($date[0])->startOfDay();
                $date_to = Carbon::parse($date[1])->endOfDay();
                return $query->whereBetween('created_at', [$date_from, $date_to]);
            })
            ->select('id', 'jama_id', 'naam_id', 'amount', 'comments', 'type', 'created_at')
            ->orderby('created_at', 'desc')
            ->paginate($limit);

        return  $transactions;
    }

    public function save(Request $request)
    {
        $validatedData = $request->validate([
            'jama_account' => 'required|numeric',
            'naam_account' => 'required|numeric',
            'amount' => 'required|numeric',
            'sale_id' => (string) Str::orderedUuid(),
        ]);

        DB::beginTransaction();

        try {
            $jama_account = $request->get('jama_account');
            $naam_account = $request->get('naam_account');
            $amount = $request->get('amount');
            $comment = $request->get('comments');
            $transaction = $this->doTransaction($naam_account, $jama_account, $amount, 'others', '', $comment);
            DB::commit();
            return response()->json(['transactions' => $transaction]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 403);
        }
    }

    public function setBalance($user_id, $type, $amount)
    {
        $balance = Balance::firstOrNew(['user_id' => $user_id]);
        $balance->user_id = $user_id;
        if ($type == 'naam') {
            $balance->naam += $amount;
            $balance->balance -= $amount;
        } else {
            $balance->jama += $amount;
            $balance->balance += $amount;
        }
        $balance->save();
    }

    public function show($id)
    {
        // Not implemented
    }

    public function update(Request $request, $id)
    {
        // Not implemented
    }

    public function destroy($id)
    {
        $trans = ATrans::find($id);
        $trans->status = ($trans->status == 'disable') ? 'enable' : 'disable';
        $trans->save();
    }

    public function getKhataDetails(Request $request)
    {
        $date = $request->get('daterange');
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $id = (int) $request->id;
        $acc_total = $this->accTotal($id);
        $transactions = ATrans::when($date, function ($q) use ($date) {
            $datefrom = Carbon::parse($date[0])->startOfDay();
            $dateto = Carbon::parse($date[1])->endOfDay();
            return $q->whereBetween('date', array($datefrom, $dateto));
        })
            ->where(function ($query) use ($id) {
                $query->where("jama_account", $id)
                    ->orWhere("naam_account", $id);
            })
            ->where('status', 'enable')
            ->selectRaw('sum(amount) as amount, jama_account, naam_account,comments, entry_by, created_at, updated_at')
            ->orderby('created_at', 'desc')
            ->orderby('sale_id', 'desc')
            ->groupBy(
                DB::raw(
                    'if (sale_id IS NULL, id, sale_id),naam_account,sale_id,purchase_id,naam_account'
                )
            )
            ->paginate($limit);

        $trans_collection_last_date = $transactions->getCollection()->first()->created_at;
        $total_last_date_on_page = $this->accTotalFromDate($id, $trans_collection_last_date);
        $transactions->getCollection()->transform(function ($transaction) use ($total_last_date_on_page, &$balance, $id) {
            if (!isset($balance))
                $balance = $total_last_date_on_page[0]->acc_total;

            $transaction['balance'] =  $balance;
            if ($transaction->jama_account == $id) {
                $balance -= $transaction->amount;
            } else {
                $balance += $transaction->amount;
            }
            return $transaction;
        });

        return response()->json(['data' => $transactions, 'acc_total' => $acc_total, 'last_date_total' => $total_last_date_on_page]);
    }

    public function getKhataDetailsDate(Request $request)
    {
        $date = $request->get('daterange');
        $date_from = Carbon::parse($date[0])->startOfDay();
        $date_to = Carbon::parse($date[1])->endOfDay();
        $request->id = 1;
        $id = $request->id;
        $searchParams = $request->all();
        $limit = Arr::get($searchParams, 'limit', static::ITEM_PER_PAGE);
        $transactions = ATrans::whereDate('created_at', '>=', $date_from)
            ->whereDate('created_at', '<=', $date_to)
            ->where(function ($query) use ($id) {
                $query->where("jama_account", $id)
                    ->orWhere("naam_account", $id);
            })
            ->orderby('created_at', 'desc')
            ->where('status', 'enable')
            ->paginate($limit);

        return response()->json(['data' => $transactions]);
    }

    public function accTotalFromDate($id, $fromdate)
    {
        $balance =  DB::select("SELECT IFNULL(sum(IFNULL(totaljama,0)-IFNULL(totalnaam,0)),0) as acc_total from 
          (SELECT sum(amount) as totaljama from account_transactions where jama_account = $id AND status='enable' AND created_at <= '$fromdate') as jama 
          JOIN 
          (SELECT sum(amount) as totalnaam from account_transactions where naam_account = $id AND status='enable'  AND created_at <= '$fromdate') as naam");
        return  $balance;
    }

    public function accTotal($id, $ajax = false)
    {
        $balance =  DB::select("SELECT IFNULL(sum(IFNULL(totaljama,0)-IFNULL(totalnaam,0)),0) as acc_total from 
         (SELECT sum(amount) as totaljama from account_transactions where jama_account = $id AND status='enable') as jama 
         JOIN 
         (SELECT sum(amount) as totalnaam from account_transactions where naam_account = $id AND status='enable') as naam");

        if ($ajax)
            return response()->json(['prev_balnace' => $balance]);
        return  $balance;
    }

    public function udharTotal()
    {
        $udhar =  DB::select("SELECT name, phone,address,
                (
                    IFNULL((select sum(amount) from account_transactions act where status = 'enable' AND act.jama_account = a.id),0) 
                    - 
                    IFNULL((select sum(amount) from account_transactions act where status = 'enable' AND act.naam_account = a.id),0)
                ) as total from accounts a WHERE a.id NOT IN(1,2) GROUP by a.id ORDER by total ASC");
        return response()->json(['udhar' => $udhar]);
    }
}
