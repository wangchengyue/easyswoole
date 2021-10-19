<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * SystemModel
 * Class SystemModel
 * Create With ClassGeneration
 * @property int $id // 管理员操作记录ID
 * @property int $admin_id // 管理员id
 * @property string $admin_name // 管理员姓名
 * @property string $path // 链接
 * @property string $page // 行为
 * @property string $method // 访问类型
 * @property string $ip // 登录IP
 * @property string $type // 类型
 * @property int $add_time // 操作时间
 * @property int $merchant_id // 商户id
 */
class SystemModel extends AbstractModel
{
	protected $tableName = 'eb_system_log';


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
		int $admin_id,
		string $admin_name,
		string $path,
		string $page,
		string $method,
		string $ip,
		string $type,
		int $add_time,
		int $merchant_id
	): self {
		$data = [
		    'admin_id'=>$admin_id,
		    'admin_name'=>$admin_name,
		    'path'=>$path,
		    'page'=>$page,
		    'method'=>$method,
		    'ip'=>$ip,
		    'type'=>$type,
		    'add_time'=>$add_time,
		    'merchant_id'=>$merchant_id,
		];
		$model = new self($data);
		$model->save();
		return $model;
	}
}

