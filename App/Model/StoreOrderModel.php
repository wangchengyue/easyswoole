<?php

namespace App\Model;

use EasySwoole\ORM\AbstractModel;

/**
 * StoreOrderModel
 * Class StoreOrderModel
 * Create With ClassGeneration
 * @property int $id // 订单ID
 * @property string $order_id // 订单号
 * @property int $uid // 用户id
 * @property string $real_name // 用户姓名
 * @property string $user_phone // 用户电话
 * @property string $user_address // 详细地址
 * @property string $cart_id // 购物车id
 * @property float $freight_price // 运费金额
 * @property int $total_num // 订单商品总数
 * @property float $total_price // 订单总价
 * @property float $total_postage // 邮费
 * @property float $pay_price // 实际支付金额
 * @property float $pay_postage // 支付邮费
 * @property float $deduction_price // 抵扣金额
 * @property int $coupon_id // 优惠券id
 * @property float $coupon_price // 优惠券金额
 * @property int $paid // 支付状态
 * @property int $pay_time // 支付时间
 * @property string $pay_type // 支付方式
 * @property int $add_time // 创建时间
 * @property int $status // 订单状态（-1 : 申请退款 -2 : 退货成功 0：待发货；1：待收货；2：已收货；3：待评价；-1：已退款）
 * @property int $refund_status // 0 未退款 1 申请中 2 已退款
 * @property string $refund_reason_wap_img // 退款图片
 * @property string $refund_reason_wap_explain // 退款用户说明
 * @property int $refund_reason_time // 退款时间
 * @property string $refund_reason_wap // 前台退款原因
 * @property string $refund_reason // 不退款的理由
 * @property float $refund_price // 退款金额
 * @property string $delivery_name // 快递名称/送货人姓名
 * @property string $delivery_type // 发货类型
 * @property string $delivery_id // 快递单号/手机号
 * @property float $gain_integral // 消费赚取积分
 * @property float $use_integral // 使用积分
 * @property float $back_integral // 给用户退了多少积分
 * @property string $mark // 备注
 * @property int $is_del // 是否删除
 * @property string $unique // 唯一id(md5加密)类似id
 * @property string $remark // 管理员备注
 * @property int $mer_id // 商户ID
 * @property int $is_mer_check //
 * @property int $combination_id // 拼团产品id0一般产品
 * @property int $pink_id // 拼团id 0没有拼团
 * @property float $cost // 成本价
 * @property int $seckill_id // 秒杀产品ID
 * @property int $bargain_id // 砍价id
 * @property string $verify_code // 核销码
 * @property int $store_id // 门店id
 * @property int $shipping_type // 配送方式 1=快递 ，2=门店自提
 * @property int $is_channel // 支付渠道(0微信公众号1微信小程序)
 * @property int $is_remind // 消息提醒
 * @property int $is_system_del // 后台是否删除
 */
class StoreOrderModel extends AbstractModel
{
	protected $tableName = 'eb_store_order';


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
		string $order_id,
		int $uid,
		string $real_name,
		string $user_phone,
		string $user_address,
		string $cart_id,
		float $freight_price,
		int $total_num,
		float $total_price,
		float $total_postage,
		float $pay_price,
		float $pay_postage,
		float $deduction_price,
		int $coupon_id,
		float $coupon_price,
		int $paid,
		int $pay_time,
		string $pay_type,
		int $add_time,
		int $status,
		int $refund_status,
		string $refund_reason_wap_img,
		string $refund_reason_wap_explain,
		int $refund_reason_time,
		string $refund_reason_wap,
		string $refund_reason,
		float $refund_price,
		string $delivery_name,
		string $delivery_type,
		string $delivery_id,
		float $gain_integral,
		float $use_integral,
		float $back_integral,
		string $mark,
		int $is_del,
		string $unique,
		string $remark,
		int $mer_id,
		int $is_mer_check,
		int $combination_id,
		int $pink_id,
		float $cost,
		int $seckill_id,
		int $bargain_id,
		string $verify_code,
		int $store_id,
		int $shipping_type,
		int $is_channel,
		int $is_remind,
		int $is_system_del
	): self {
		$data = [
		    'order_id'=>$order_id,
		    'uid'=>$uid,
		    'real_name'=>$real_name,
		    'user_phone'=>$user_phone,
		    'user_address'=>$user_address,
		    'cart_id'=>$cart_id,
		    'freight_price'=>$freight_price,
		    'total_num'=>$total_num,
		    'total_price'=>$total_price,
		    'total_postage'=>$total_postage,
		    'pay_price'=>$pay_price,
		    'pay_postage'=>$pay_postage,
		    'deduction_price'=>$deduction_price,
		    'coupon_id'=>$coupon_id,
		    'coupon_price'=>$coupon_price,
		    'paid'=>$paid,
		    'pay_time'=>$pay_time,
		    'pay_type'=>$pay_type,
		    'add_time'=>$add_time,
		    'status'=>$status,
		    'refund_status'=>$refund_status,
		    'refund_reason_wap_img'=>$refund_reason_wap_img,
		    'refund_reason_wap_explain'=>$refund_reason_wap_explain,
		    'refund_reason_time'=>$refund_reason_time,
		    'refund_reason_wap'=>$refund_reason_wap,
		    'refund_reason'=>$refund_reason,
		    'refund_price'=>$refund_price,
		    'delivery_name'=>$delivery_name,
		    'delivery_type'=>$delivery_type,
		    'delivery_id'=>$delivery_id,
		    'gain_integral'=>$gain_integral,
		    'use_integral'=>$use_integral,
		    'back_integral'=>$back_integral,
		    'mark'=>$mark,
		    'is_del'=>$is_del,
		    'unique'=>$unique,
		    'remark'=>$remark,
		    'mer_id'=>$mer_id,
		    'is_mer_check'=>$is_mer_check,
		    'combination_id'=>$combination_id,
		    'pink_id'=>$pink_id,
		    'cost'=>$cost,
		    'seckill_id'=>$seckill_id,
		    'bargain_id'=>$bargain_id,
		    'verify_code'=>$verify_code,
		    'store_id'=>$store_id,
		    'shipping_type'=>$shipping_type,
		    'is_channel'=>$is_channel,
		    'is_remind'=>$is_remind,
		    'is_system_del'=>$is_system_del,
		];
		$model = new self($data);
		$model->save();
		return $model;
	}
}

