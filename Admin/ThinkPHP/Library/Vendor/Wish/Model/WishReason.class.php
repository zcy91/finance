<?php 
/**
 * Copyright 2014 Wish.com, ContextLogic or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at 
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
namespace Wish\Model;


class WishReason{
    const REASON_FUHAO1 = '其他';
    const REASON_1 = '店铺无法履行订单';        //Unable to fulfill order
    const REASON_18 = '误下单了';
    const REASON_20 = '配送时间过长';           //Item did not arrive on time
    const REASON_22 = '商品不合适';
    const REASON_23 = '收到错误的商品';
    const REASON_24 = '商品为假冒伪劣品';       //Item is counterfeit
    const REASON_25 = '商品已损坏';         
    const REASON_26 = '商品与描述不符';
    const REASON_27 = '商品与清单不符';         //Item does not match the listing
    const REASON_30 = '产品被配送至错误的地址';  //Item was delivered to the wrong address
    const REASON_31 = '用户提供了错误的地址';
    const REASON_32 = '商品退还至发货人';
    const REASON_33 = 'Incomplete Order';
    const REASON_34 = '店铺无法履行订单';
    const REASON_35 = '此件显示已妥投，但客户未收到。';
    const REASON_1001 = 'Received the wrong color';
    const REASON_1002 = 'Item is of poor quality';
    const REASON_1004 = 'Product listing is missing information';
    const REASON_1005 = 'Item did not meet expectations';
    const REASON_1006 = 'Package was empty';
}