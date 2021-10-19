<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * AdaTradeModel
 * Class AdaTradeModel
 * Create With ClassGeneration
 * @property int $id //
 * @property string $trade // 交易方向
 * @property int $tradeID // 交易ID
 * @property mixed $addtime // 交易时间
 * @property string $nums // 交易数量
 * @property string $trade_price //
 * @property string $ts //
 */
class AdaTradeModel extends AbstractModel
{
	protected $tableName = 'eb_ada_trade';


	public function getList(int $page = 1, int $pageSize = 10, string $field = '*'): array
	{
		$list = $this
		    ->withTotalCount()
			->order($this->schemaInfo()->getPkFiledName(), 'DESC')
		    ->field($field)
		    ->page($page, $pageSize)
		    ->all();
		$total = $this->lastQueryResult()->getTotalCount();
		$data = [
		    'page'=>$page,
		    'pageSize'=>$pageSize,
		    'list'=>$list,
		    'total'=>$total,
		    'pageCount'=>ceil($total / $pageSize)
		];
		return $data;
	}


	public function addData(
		string $trade,
		int $tradeID,
		mixed $addtime,
		string $nums,
		string $trade_price,
		string $ts
	): self {
		$data = [
		    'trade'=>$trade,
		    'tradeID'=>$tradeID,
		    'addtime'=>$addtime,
		    'nums'=>$nums,
		    'trade_price'=>$trade_price,
		    'ts'=>$ts,
		];
		$model = new self($data);
		$model->save();
		return $model;
	}
}

