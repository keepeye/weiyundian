<?php
class WeixinAction extends Action
{
    private $token;
    private $fun;
    private $data = array();
    private $my = '微信机器人';
    public function index()
    {
        $this->token = $this->_get('token');
        $weixin      = new Wechat($this->token);
        $this->data  = $weixin->request();
        
        $this->my    = C('site_my');
        list($content, $type) = $this->reply($this->data);
        $weixin->response($content, $type);
    }
    
    private function reply($data)
    {
		Log::write($data['Event'],Log::INFO);
	    if ('CLICK' == $data['Event'])
	    {
		    $data['Content'] = $data['EventKey'];
	    }
		//用户关注时事件推送
	    if ('subscribe' == $data['Event']) 
	    {
		    $this->requestdata('follownum');
		    $data = M('Areply')->field('home,keyword,content,status')->where(array(
					    'token' => $this->token
					    ))->find();
			if ($data['status'] == 1)
			{
			
			    return array(
					    $data['content'],
					    'text'
					);
			}
		    if ($data['keyword'] == '首页' || $data['keyword'] == 'home') {
			    return $this->shouye();
		    }
		    if ($data['home'] == 1) {
			    $like['keyword'] = array(
					    'like',
					    '%' . $data['keyword'] . '%'
					    );
			    $like['token']   = $this->token;
			    $back            = M('Img')->field('id,text,pic,url,title')->limit(9)->order('id desc')->where($like)->select();
			    foreach ($back as $keya => $infot) {
				    if ($infot['url'] != false) {
					    $url = $infot['url'];
				    } else {
					    $url = rtrim(C('site_url'), '/') . U('Wap/Index/content', array(
								    'token' => $this->token,
								    'id' => $infot['id']
								    ));
				    }
				    $return[] = array(
						    $infot['title'],
						    $infot['text'],
						    $infot['pic'],
						    $url
						    );
			    }
			    return array(
					    $return,
					    'news'
					);
		    } 
		    else
		    {
			    return array(
					    $data['content'],
					    'text'
					);
		    }
	    }
		//用户取消关注时的事件
	    elseif ('unsubscribe' == $data['Event'])
	    {
		    $this->requestdata('unfollownum');
	    }
	    //处理普通请求
		$check = $this->user('connectnum');//初始化或检测商户请求数
		//如果connectnum值为1表示请求数未满，并自动加1,否则提示已经用完请求数。
		if ($check['connectnum'] != 1)
        {
            return array(
                    "商户请求数已经用完,请联系商户",
                    'text'
                    );

        }

	    $Pin       = new GetPin();
	    $key       = $data['Content'];
	    $open      = M('Token_open')->where(array(
					'token' => $this->_get('token')
				    ))->find();
	    $this->fun = $open['queryname'];
	    $datafun   = explode(',', $open['queryname']);//获取用户开启的功能
	    $tags      = $this->get_tags($key);//对用户发送的内容进行分词
	    $back      = explode(',', $tags);//将分词结果转换为数组
		//遍历分词结果tags
	    foreach ($back as $keydata => $data)
	    {
		    $string = $Pin->Pinyin($data);//将单词转换为拼音
			//如果拼音等于用户开启的某个功能拼音
		    if (in_array($string, $datafun))
		    {
			    //$check = $this->user('connectnum');//检测连接数
			    if ($string == 'fujin') {//如果单词是附近
				    $this->recordLastRequest($key);
			    }
			    $this->requestdata('textnum');
			    if ($check['connectnum'] != 1) {
				    $return = C('connectout');
				    continue;
			    }
			    unset($back[$keydata]);//将这个词从分词数组中删除
			    //eval('$return= $this->' . $string . '($back);');//调用对应功能的方法，得到返回结果
			    $return = $this->$string($back);

			    continue;
		    }
	    }
		//如果return不为空，则表示关键词符合某个功能，所以直接返回结果，优先级最高。
	    if (!empty($return))
		{
			if (is_array($return))
			{
				return $return;
			} 
			else
			{
				return array(
						$return,
						'text'
						);
			}
		}
		else 
		{
			if ($this->data['Location_X']) {
				$this->recordLastRequest($this->data['Location_Y'] . ',' . $this->data['Location_X'], 'location');
				return $this->map($this->data['Location_X'], $this->data['Location_Y']);
			}
			if (!(strpos($key, '开车去') === FALSE) || !(strpos($key, '坐公交') === FALSE) || !(strpos($key, '步行去') === FALSE)) {
				$this->recordLastRequest($key);
				$user_request_model = M('User_request');
				$loctionInfo        = $user_request_model->where(array(
							'token' => $this->_get('token'),
							'msgtype' => 'location',
							'uid' => $this->data['FromUserName']
							))->find();
				if ($loctionInfo && intval($loctionInfo['time'] > (time() - 60))) {
					$latLng = explode(',', $loctionInfo['keyword']);
					return $this->map($latLng[1], $latLng[0]);
				}
				return array(
						'请发送您所在的位置',
						'text'
						);
			}
			switch ($key) {
				case '首页':
					return $this->home();
					break;
				case '主页':
					return $this->home();
					break;
				case '地图':
					return $this->companyMap();
				case '最近的':
					$this->recordLastRequest($key);
					$user_request_model = M('User_request');
					$loctionInfo        = $user_request_model->where(array(
								'token' => $this->_get('token'),
								'msgtype' => 'location',
								'uid' => $this->data['FromUserName']
								))->find();
					if ($loctionInfo && intval($loctionInfo['time'] > (time() - 60))) {
						$latLng = explode(',', $loctionInfo['keyword']);
						return $this->map($latLng[1], $latLng[0]);
					}
					return array(
							'请发送您所在的位置',
							'text'
							);
					break;
				case '帮助':
				case 'help':
					return $this->help();
					break;
				case '会员卡':
				case '会员':
					return $this->member();
					break;
				case '3g相册':
				case '相册':
					return $this->xiangce();
					break;
                case '户型':
                    $house = M('House')->where(array(
										'token' => $this->token
										))->find();
							return array(
									array(
										array(
											$house['title'],
											$house['info'],
											$house['room_url'],
											C('site_url') . '/index.php?g=Wap&m=House&a=room&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'].'&wxref=mp.weixin.qq.com'
											)
										),
									'news'
									);

					break;
				case '房友印象':
                    $house = M('House')->where(array(
										'token' => $this->token
										))->find();
							return array(
									array(
										array(
											'房友印象',
											'房友印象，专家点评',
											$house['picurl'],
											C('site_url') . '/index.php?g=Wap&m=House&a=review&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'].'&wxref=mp.weixin.qq.com'
											)
										),
									'news'
									);

					break;
                case '房产':
                    $house = M('House')->where(array(
										'token' => $this->token
										))->find();
							return array(
									array(
										array(
											$house['title'],
											$house['info'],
											$house['picurl'],
											C('site_url') . '/index.php?g=Wap&m=House&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'].'&wxref=mp.weixin.qq.com'
											)
										),
									'news'
									);

					break;


				case '商城':
					$pro = M('reply_info')->where(array(
								'infotype' => 'Shop',
								'token' => $this->token
								))->find();
					return array(
							array(
								array(
									$pro['title'],
									strip_tags(htmlspecialchars_decode($pro['info'])),
									$pro['picurl'],
									C('site_url') . '/index.php?g=Wap&m=Product&a=cats&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'].'&wxref=mp.weixin.qq.com'
									)
								),
							'news'
							);
					break;
				case '旅游':
				case '旅游线路':
					$pro = M('reply_info')->where(array(
								'infotype' => 'Travel',
								'token' => $this->token
								))->find();
					return array(
							array(
								array(
									$pro['title'],
									strip_tags(htmlspecialchars_decode($pro['info'])),
									$pro['picurl'],
									C('site_url') . '/index.php?g=Wap&m=Travel&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'].'&wxref=mp.weixin.qq.com'
									)
								),
							'news'
							);
					break;
				case '4s服务':
				case '汽车服务':
				case '4s店':
					$pro = M('reply_info')->where(array(
								'infotype' => 'Car',
								'token' => $this->token
								))->find();
					return array(
							array(
								array(
									$pro['title'],
									strip_tags(htmlspecialchars_decode($pro['info'])),
									$pro['picurl'],
									C('site_url') . '/index.php?g=Wap&m=Car&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'].'&wxref=mp.weixin.qq.com'
									)
								),
							'news'
							);
					break;
	/*			case 'aaa':
					return array(
							array(
								array(
									$pro['title'],
									strip_tags(htmlspecialchars_decode($pro['info'])),
									$pro['picurl'],
									C('site_url') . '/cms/index.php?token=' . $this->token . '&wecha_id=' . $this->data['FromUserName']
									)
								),
							'news'
							);
					break;
				case '订餐':
					$pro = M('reply_info')->where(array(
								'infotype' => 'Dining',
								'token' => $this->token
								))->find();
					return array(
							array(
								array(
									$pro['title'],
									strip_tags(htmlspecialchars_decode($pro['info'])),
									$pro['picurl'],
									C('site_url') . '/index.php?g=Wap&m=Product&a=dining&dining=1&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName']
									)
								),
							'news'
							);
					break;
				case '团购':
					$pro = M('reply_info')->where(array(
								'infotype' => 'Groupon',
								'token' => $this->token
								))->find();
					return array(
							array(
								array(
									$pro['title'],
									strip_tags(htmlspecialchars_decode($pro['info'])),
									$pro['picurl'],
									C('site_url') . '/index.php?g=Wap&m=Groupon&a=grouponIndex&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName']
									)
								),
							'news'
							);
					break;*/
				default:
					$check = $this->user('diynum', $key);
					if ($check['diynum'] != 1) {
						return array(
								C('connectout'),
								'text'
								);
					} else {
						return $this->keyword($key);
					}
			}
		}
    }
    function xiangce()
    {
        $photo           = M('Photo')->where(array(
            'token' => $this->token,
            'status' => 1
        ))->find();
        $data['title']   = $photo['title'];
        $data['keyword'] = $photo['info'];
        $data['url']     = rtrim(C('site_url'), '/') . U('Wap/Photo/index', array(
            'token' => $this->token,
            'wecha_id' => $this->data['FromUserName'],
			'wxref'=>'mp.weixin.qq.com'
        ));
        $data['picurl']  = $photo['picurl'] ? $photo['picurl'] : rtrim(C('site_url'), '/') . '/tpl/static/images/yj.jpg';
        return array(
            array(
                array(
                    $data['title'],
                    $data['keyword'],
                    $data['picurl'],
                    $data['url']
                )
            ),
            'news'
        );
    }
    function companyMap()
    {
        import("Home.Action.MapAction");
        $mapAction = new MapAction();
        return $mapAction->staticCompanyMap();
    }
    function shenhe($name)
    {
        $name = implode('', $name);
        if (empty($name)) {
            return '正确的审核帐号方式是：审核+帐号';
        } else {
            $user = M('Users')->field('id')->where(array(
                'username' => $name
            ))->find();
            if ($user == false) {
                return '主人' . $this->my . "提醒您,您还没注册吧\n正确的审核帐号方式是：审核+帐号,不含+号";
            } else {
                $up = M('users')->where(array(
                    'id' => $user['id']
                ))->save(array(
                    'status' => 1,
                    'viptime' => strtotime("+1 day")
                ));
                if ($up != false) {
                    return '主人' . $this->my . '恭喜您,您的帐号已经审核,您现在可以登陆平台测试功能啦!';
                } else {
                    return '服务器繁忙请稍后再试';
                }
            }
        }
    }
    function huiyuanka($name)
    {
        return $this->member();
    }
    function member()
    {
        $card     = M('member_card_create')->where(array(
            'token' => $this->token,
            'wecha_id' => $this->data['FromUserName']
        ))->find();
        $cardInfo = M('member_card_set')->where(array(
            'token' => $this->token
        ))->find();
        $memCardInfo=M('Member_card_info')->where(array('token'=>$this->token))->find();
        if ($card == false) {
            $data['picurl']  = rtrim(C('site_url'), '/') . '/tpl/static/images/member.jpg';
            $data['title']   = $memCardInfo['weixin_title'];
            $data['keyword'] =  $memCardInfo['weixin_description'];
            $data['url']     = rtrim(C('site_url'), '/') . U('Wap/Card/get_card', array(
                'token' => $this->token,
                'wecha_id' => $this->data['FromUserName'],
				'wxref'=>'mp.weixin.qq.com'
            ));
        } else {
            $data['picurl']  = rtrim(C('site_url'), '/') . '/tpl/static/images/vip.jpg';
            $data['title']   = $cardInfo['cardname'];
            $data['keyword'] = $cardInfo['msg'];
            $data['url']     = rtrim(C('site_url'), '/') . U('Wap/Card/vip', array(
                'token' => $this->token,
                'wecha_id' => $this->data['FromUserName'],
				'wxref'=>'mp.weixin.qq.com'
            ));
        }
        return array(
            array(
                array(
                    $data['title'],
                    $data['keyword'],
                    $data['picurl'],
                    $data['url']
                )
            ),
            'news'
        );
    }
    function taobao($name)
    {
        $name = array_merge($name);
        $data = M('Taobao')->where(array(
            'token' => $this->token
        ))->find();
        if ($data != false) {
            if (strpos($data['keyword'], $name)) {
                $url = $data['homeurl'] . '/search.htm?search=y&keyword=' . $name . '&lowPrice=&highPrice='.'&wxref=mp.weixin.qq.com';
            } else {
                $url = $data['homeurl'];
            }
            return array(
                array(
                    array(
                        $data['title'],
                        $data['keyword'],
                        $data['picurl'],
                        $url
                    )
                ),
                'news'
            );
        } else {
            return '商家还未及时更新淘宝店铺的信息,回复帮助,查看功能详情';
        }
    }
    function choujiang($name)
    {
        $data = M('lottery')->field('id,keyword,info,title,starpicurl')->where(array(
            'token' => $this->token,
            'status' => 1,
            'type' => 1
        ))->order('id desc')->find();
        if ($data == false) {
            return array(
                '暂无抽奖活动',
                'text'
            );
        }
        $pic = $data['starpicurl'] ? $data['starpicurl'] : rtrim(C('site_url'), '/') . '/tpl/User/default/common/images/img/activity-lottery-start.jpg';
        $url = rtrim(C('site_url'), '/') . U('Wap/Lottery/index', array(
            'type' => 1,
            'token' => $this->token,
            'id' => $data['id'],
            'wecha_id' => $this->data['FromUserName'],
			'wxref'=>'mp.weixin.qq.com'
        ));
        return array(
            array(
                array(
                    $data['title'],
                    $data['info'],
                    $pic,
                    $url
                )
            ),
            'news'
        );
    }
    function keyword($key)
	{
		Log::write($key,Log::INFO);
		$like['token'] = $this->token;
		$like['keyword'] = array('like','%'.$key.'%');
		$data = M('keyword')->where($like)->find();
		Log::write($this->token,Log::INFO);
		if ($data != false) 
		{
			switch ($data['module']) 
			{
				case 'Img':
					$this->requestdata('imgnum');
					$img_db   = M($data['module']);
					$back     = $img_db->field('id,text,pic,url,title')->limit(9)->order('id desc')->where($like)->select();
					$idsWhere = 'id in (';
							$comma    = '';
							foreach ($back as $keya => $infot) {
							$idsWhere .= $comma . $infot['id'];
							$comma = ',';
							if ($infot['url'] != false) {
							if (!(strpos($infot['url'], 'http') === FALSE)) {
							$url = $infot['url'];
							} else {
							$urlInfos = explode(' ', $infot['url']);
							switch ($urlInfos[0]) {
							case '刮刮卡':
							$url = C('site_url') . U('Wap/Guajiang/index', array(
									'token' => $this->token,
									'wecha_id' => $this->data['FromUserName'],
									'id' => $urlInfos[1],
									'wxref'=>'mp.weixin.qq.com'
									));
							break;
							case '大转盘':
							$url = C('site_url') . U('Wap/Lottery/index', array(
									'token' => $this->token,
									'wecha_id' => $this->data['FromUserName'],
									'id' => $urlInfos[1],
									'wxref'=>'mp.weixin.qq.com'
									));
							break;
							case '商家订单':
							$url = C('site_url') . '/index.php?g=Wap&m=Host&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&hid=' . $urlInfos[1].'&wxref=mp.weixin.qq.com';
							break;
							case '优惠券':
							$url = C('site_url') . U('Wap/Coupon/index', array(
										'token' => $this->token,
										'wecha_id' => $this->data['FromUserName'],
										'id' => $urlInfos[1],
										'wxref'=>'mp.weixin.qq.com'
										));
							break;
							}
							}
							} else {

								$url = rtrim(C('site_url'), '/') . U('Wap/Index/content', array(
											'token' => $this->token,
											'id' => $infot['id'],
											'wxref'=>'mp.weixin.qq.com'
											));
								Log::write($url,Log::INFO);
							}
							$return[] = array(
									$infot['title'],
									$infot['text'],
									$infot['pic'],
									$url
									);
							}
							$idsWhere .= ')';
							if ($back) {
								$img_db->where($idsWhere)->setInc('click');
							}
							return array(
									$return,
									'news'
									);
							break;
				case 'Host':
							$this->requestdata('other');
							$host = M('Host')->where(array(
										'id' => $data['pid']
										))->find();
							return array(
									array(
										array(
											$host['title'],
											$host['info'],
											$host['ppicurl'],
											C('site_url') . '/index.php?g=Wap&m=Host&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&hid=' . $data['pid'].'&wxref=mp.weixin.qq.com'
											)
										),
									'news'
									);
							break;
                case 'House':
							$this->requestdata('other');
							$house = M('House')->where(array(
										'id' => $data['pid']
										))->find();
							return array(
									array(
										array(
											$house['title'],
											$house['info'],
											$house['picurl'],
											C('site_url') . '/index.php?g=Wap&m=House&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'].'&wxref=mp.weixin.qq.com'
											)
										),
									'news'
									);
							break;

				case 'Text':
							$this->requestdata('textnum');
							$info = M($data['module'])->order('id desc')->find($data['pid']);
							return array(
									htmlspecialchars_decode($info['text']),
									'text'
									);
							break;
				case 'Product':
							$this->requestdata('other');
							$pro = M('Product')->where(array(
										'id' => $data['pid']
										))->find();
							return array(
									array(
										array(
											$pro['name'],
											strip_tags(htmlspecialchars_decode($pro['intro'])),
											$pro['logourl'],
											C('site_url') . '/index.php?g=Wap&m=Product&a=product&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'].'&wxref=mp.weixin.qq.com'
											)
										),
									'news'
									);
							break;
				case 'Selfform':
							$this->requestdata('other');
							$pro = M('Selfform')->where(array(
										'id' => $data['pid']
										))->find();
                            
		                    Log::write($data['pid'],Log::INFO);
							return array(
									array(
										array(
											$pro['name'],
											strip_tags(htmlspecialchars_decode($pro['intro'])),
											$pro['logourl'],
											C('site_url') . '/index.php?g=Wap&m=Selfform&a=index&token=' . $this->token . '&wecha_id=' . $this->data['FromUserName'] . '&id=' . $data['pid'].'&wxref=mp.weixin.qq.com'
											)
										),
									'news'
									);
							break;
				case 'Lottery':
							$this->requestdata('other');
							$info = M('Lottery')->find($data['pid']);
							if ($info == false || $info['status'] == 3) {
								return array(
										'活动可能已经结束或者被删除了',
										'text'
										);
							}
							switch ($info['type']) {
								case 1:
									$model = 'Lottery';
									break;
								case 2:
									$model = 'Guajiang';
									break;
								case 3:
									$model = 'Coupon';
							}
							$id   = $info['id'];
							$type = $info['type'];
							if ($info['status'] == 1) {
								$picurl = $info['starpicurl'];
								$title  = $info['title'];
								$id     = $info['id'];
								$info   = $info['info'];
							} else {
								$picurl = $info['endpicurl'];
								$title  = $info['endtite'];
								$info   = $info['endinfo'];
							}
							$url = C('site_url') . U('Wap/' . $model . '/index', array(
										'token' => $this->token,
										'type' => $type,
										'wecha_id' => $this->data['FromUserName'],
										'id' => $id,
										'type' => $type,
										'wxref'=>'mp.weixin.qq.com'
										));
							return array(
									array(
										array(
											$title,
											$info,
											$picurl,
											$url
											)
										),
									'news'
									);
				default:
							$this->requestdata('videonum');
							$info = M($data['module'])->order('id desc')->find($data['pid']);
							return array(
									array(
										$info['title'],
										$info['keyword'],
										$info['musicurl'],
										$info['hqmusicurl']
										),
									'music'
									);
			}
		} 
		else
		{
			//判断是否有与关键词匹配的分类，有则返回分类列表的链接
			$classify = M('Classify')->field('url,id,type,name,info,img')->where(array('token'=>$this->token,'name'=>$key))->find();
			if(!empty($classify)){
				$href = WeParseUrl($classify['url'],$classify['type'],array('token'=>$this->token,'wecha_id'=>$this->data['FromUserName'],'classid'=>$classify['id'],'wxref'=>'mp.weixin.qq.com'));
				return array(
					array(
						array(
							$classify['name'],//自动回复信息，分类名
							$classify['info'],//描述信息
							$classify['img'],//图片链接
							C('site_url') . $href,//网页链接
							)
						),
					'news'
				);
			}
			//
			if (false === strpos($this->fun, 'liaotian')) {//这里应该用false严格判断，因为如果liaotian出现在字符串开头返回的将是0。
				$other = M('Other')->where(array(
							'token' => $this->token
							))->find();
				if ($other == false) {
					return array(
							'回复帮助，可了解所有功能',
							'text'
							);
				} else {
					if (empty($other['keyword'])) {
						return array(
								$other['info'],
								'text'
								);
					} else {
						$img = M('Img')->field('id,text,pic,url,title')->limit(5)->order('id desc')->where(array(
									'token' => $this->token,
									'keyword' => array(
										'like',
										'%' . $other['keyword'] . '%'
										)
									))->select();
						if ($img == false) {
							return array(
									'无此图文信息,请提醒商家，重新设定关键词',
									'text'
									);
						}
						foreach ($img as $keya => $infot) {
							if ($infot['url'] != false) {
								$url = $infot['url'];
							} else {
								$url = rtrim(C('site_url'), '/') . U('Wap/Index/content', array(
											'token' => $this->token,
											'id' => $infot['id'],
											'wxref'=>'mp.weixin.qq.com'
											));
							}
							$return[] = array(
									$infot['title'],
									$infot['text'],
									$infot['pic'],
									$url
									);
						}
						return array(
								$return,
								'news'
								);
					}
				}
			}
			return array(
					$this->chat($key),
					'text'
					);
		}
	}
    function home()
    {
        return $this->shouye();
    }
    function shouye($name)
    {
        $home = M('Home')->where(array(
            'token' => $this->token
        ))->find();
        if ($home == false) {
            return array(
                '商家未做首页配置，请稍后再试',
                'text'
            );
        } else {
            $imgurl = $home['picurl'];
            if ($home['apiurl'] == false) {
                $url = rtrim(C('site_url'), '/') . '/index.php?g=Wap&m=Index&a=index&token=' . $this->token.'&wxref=mp.weixin.qq.com';
            } else {
                $url = $home['apiurl'];
            }
            $url.="&wecha_id=".$this->data['FromUserName'];
        }
        return array(
            array(
                array(
                    $home['title'],
                    $home['info'],
                    $imgurl,
                    $url
                )
            ),
            'news'
        );
    }
    function kuaidi($data)
    {
        $data = array_merge($data);
        $str  = file_get_contents('http://www.weinxinma.com/api/index.php?m=Express&a=index&name=' . $data[0] . '&number=' . $data[1]);
        return $str;
    }
    function langdu($data)
    {
        $data   = implode('', $data);
        $mp3url = 'http://www.apiwx.com/aaa.php?w=' . urlencode($data);
        return array(
            array(
                $data,
                '点听收听',
                $mp3url,
                $mp3url
            ),
            'music'
        );
    }
    function jiankang($data)
    {
        if (empty($data))
            return '主人，' . $this->my . "提醒您\n正确的查询方式是:\n健康+身高,+体重\n例如：健康170,65";
        $height  = $data[1] / 100;
        $weight  = $data[2];
        $Broca   = ($height * 100 - 80) * 0.7;
        $kaluli  = 66 + 13.7 * $weight + 5 * $height * 100 - 6.8 * 25;
        $chao    = $weight - $Broca;
        $zhibiao = $chao * 0.1;
        $res     = round($weight / ($height * $height), 1);
        if ($res < 18.5) {
            $info = '您的体形属于骨感型，需要增加体重' . $chao . '公斤哦!';
            $pic  = 1;
        } elseif ($res < 24) {
            $info = '您的体形属于圆滑型的身材，需要减少体重' . $chao . '公斤哦!';
        } elseif ($res > 24) {
            $info = '您的体形属于肥胖型，需要减少体重' . $chao . '公斤哦!';
        } elseif ($res > 28) {
            $info = '您的体形属于严重肥胖，请加强锻炼，或者使用我们推荐的减肥方案进行减肥';
        }
        return $info;
    }
    function fujin($keyword)
    {
        $keyword = implode('', $keyword);
        if ($keyword == false) {
            return $this->my . "很难过,无法识别主淫的指令,正确使用方法是:输入【附近+关键词】当" . $this->my . '提醒您输入地理位置的时候就OK啦';
        }
        $data            = array();
        $data['time']    = time();
        $data['token']   = $this->_get('token');
        $data['keyword'] = $keyword;
        $data['uid']     = $this->data['FromUserName'];
        $re              = M('Nearby_user');
        $user            = $re->where(array(
            'token' => $this->_get('token'),
            'uid' => $data['uid']
        ))->find();
        if ($user == false) {
            $re->data($data)->add();
        } else {
            $id['id'] = $user['id'];
            $re->where($id)->save($data);
        }
        return "主淫【" . $this->my . "】已经接收到你的指令\n请发送您的地理位置给我哈";
    }
    function recordLastRequest($key, $msgtype = 'text')
    {
        $rdata              = array();
        $rdata['time']      = time();
        $rdata['token']     = $this->_get('token');
        $rdata['keyword']   = $key;
        $rdata['msgtype']   = $msgtype;
        $rdata['uid']       = $this->data['FromUserName'];
        $user_request_model = M('User_request');
        $user_request_row   = $user_request_model->where(array(
            'token' => $this->_get('token'),
            'msgtype' => $msgtype,
            'uid' => $rdata['uid']
        ))->find();
        if (!$user_request_row) {
            $user_request_model->add($rdata);
        } else {
            $rid['id'] = $user_request_row['id'];
            $user_request_model->where($rid)->save($rdata);
        }
    }
    function map($x, $y)
    {
        $user_request_model = M('User_request');
        $user_request_row   = $user_request_model->where(array(
            'token' => $this->_get('token'),
            'msgtype' => 'text',
            'uid' => $this->data['FromUserName']
        ))->find();
        if (!(strpos($user_request_row['keyword'], '附近') === FALSE)) {
            $user    = M('Nearby_user')->where(array(
                'token' => $this->_get('token'),
                'uid' => $this->data['FromUserName']
            ))->find();
            $keyword = $user['keyword'];
            $radius  = 2000;
            $str     = file_get_contents(C('site_url') . '/map.php?keyword=' . urlencode($keyword) . '&x=' . $x . '&y=' . $y);
            $array   = json_decode($str);
            $map     = array();
            foreach ($array as $key => $vo) {
                $map[] = array(
                    $vo->title,
                    $key,
                    rtrim(C('site_url'), '/') . '/tpl/static/images/home.jpg',
                    $vo->url
                );
            }
            return array(
                $map,
                'news'
            );
        } else {
            import("Home.Action.MapAction");
            $mapAction = new MapAction();
            if (!(strpos($user_request_row['keyword'], '开车去') === FALSE) || !(strpos($user_request_row['keyword'], '坐公交') === FALSE) || !(strpos($user_request_row['keyword'], '步行去') === FALSE)) {
                if (!(strpos($user_request_row['keyword'], '步行去') === FALSE)) {
                    $companyid = str_replace('步行去', '', $user_request_row['keyword']);
                    if (!$companyid) {
                        $companyid = 1;
                    }
                    return $mapAction->walk($x, $y, $companyid);
                }
                if (!(strpos($user_request_row['keyword'], '开车去') === FALSE)) {
                    $companyid = str_replace('开车去', '', $user_request_row['keyword']);
                    if (!$companyid) {
                        $companyid = 1;
                    }
                    return $mapAction->drive($x, $y, $companyid);
                }
                if (!(strpos($user_request_row['keyword'], '坐公交') === FALSE)) {
                    $companyid = str_replace('坐公交', '', $user_request_row['keyword']);
                    if (!$companyid) {
                        $companyid = 1;
                    }
                    return $mapAction->bus($x, $y, $companyid);
                }
            } else {
                switch ($user_request_row['keyword']) {
                    case '最近的':
                        return $mapAction->nearest($x, $y);
                        break;
                }
            }
        }
    }
    function suanming($name)
    {
        $name = implode('', $name);
        if (empty($name)) {
            return '主人' . $this->my . '提醒您正确的使用方法是[算命+姓名]';
        }
        $data = require_once(CONF_PATH . 'suanming.php');
        $num  = mt_rand(0, 80);
        return $name . "\n" . trim($data[$num]);
    }
    function yinle($name)
    {
        $name = implode('', $name);
        $url  = 'http://httop1.duapp.com/mp3.php?musicName=' . $name;
        $str  = file_get_contents($url);
        $obj  = json_decode($str);
        return array(
            array(
                $name,
                $name,
                $obj->url,
                $obj->url
            ),
            'music'
        );
    }
    function geci($n)
    {
        $name = implode('', $n);
        @$str = 'http://api.ajaxsns.com/api.php?key=free&appid=0&msg=' . urlencode('歌词' . $name);
        $json = json_decode(file_get_contents($str));
        $str  = str_replace('{br}', "\n", $json->content);
        return str_replace('mzxing_com', 'weiyundian', $str);
    }
    function yuming($n)
    {
        $name = implode('', $n);
        @$str = 'http://api.ajaxsns.com/api.php?key=free&appid=0&msg=' . urlencode('域名' . $name);
        $json = json_decode(file_get_contents($str));
        $str  = str_replace('{br}', "\n", $json->content);
        return str_replace('mzxing_com', 'weiyundian', $str);
    }
    function tianqi($n)
    {
        $name = implode('', $n);
        @$str = 'http://api.ajaxsns.com/api.php?key=free&appid=0&msg=' . urlencode('天气' . $name);
        $json = json_decode(file_get_contents($str));
        $str  = str_replace('{br}', "\n", $json->content);
        return $str;
    }
    function shouji($n)
    {
        $name = implode('', $n);
        @$str = 'http://api.ajaxsns.com/api.php?key=free&appid=0&msg=' . urlencode('归属' . $name);
        $json = json_decode(file_get_contents($str));
        $str  = str_replace('{br}', "\n", $json->content);
        $str  = str_replace('菲菲', $this->my, str_replace('提示：', $this->my . '提醒您:', str_replace('{br}', "\n", $str)));
        return $str;
    }
    function shenfenzheng($n)
    {
        $n = implode('', $n);
        if (count($n) > 1) {
            $this->error_msg($n);
            return false;
        }
        ;
        $str1     = file_get_contents('http://www.youdao.com/smartresult-xml/search.s?jsFlag=true&type=id&q=' . $n);
        $array    = explode(':', $str1);
        $array[2] = rtrim($array[4], ",'gender'");
        $str      = trim($array[3], ",'birthday'");
        if ($str !== iconv('UTF-8', 'UTF-8', iconv('UTF-8', 'UTF-8', $str)))
            $str = iconv('GBK', 'UTF-8', $str);
        $str = '【身份证】 ' . $n . "\n" . '【地址】' . $str . "\n 【该身份证主人的生日】" . str_replace("'", '', $array[2]);
        return $str;
    }
    function gongjiao($data)
    {
        $data = array_merge($data);
        if (count($data) != 3) {
            $this->error_msg();
            return false;
        }
        ;
        $json    = file_get_contents("http://www.twototwo.cn/bus/Service.aspx?format=json&action=QueryBusByLine&key=5da453b2-b154-4ef1-8f36-806ee58580f6&zone=" . $data[0] . "&line=" . $data[1]);
        $data    = json_decode($json);
        $xianlu  = $data->Response->Head->XianLu;
        $xdata   = get_object_vars($xianlu->ShouMoBanShiJian);
        $xdata   = $xdata['#cdata-section'];
        $piaojia = get_object_vars($xianlu->PiaoJia);
        $xdata   = $xdata . ' -- ' . $piaojia['#cdata-section'];
        $main    = $data->Response->Main->Item->FangXiang;
        $xianlu  = $main[0]->ZhanDian;
        $str     = "【本公交途经】\n";
        for ($i = 0; $i < count($xianlu); $i++) {
            $str .= "\n" . trim($xianlu[$i]->ZhanDianMingCheng);
        }
        return $str;
    }
    function huoche($data, $time = '')
    {
        $data    = array_merge($data);
        $data[2] = date('Y', time()) . $time;
        if (count($data) != 3) {
            $this->error_msg($data[0] . '至' . $data[1]);
            return false;
        }
        ;
        $time = empty($time) ? date('Y-m-d', time()) : date('Y-', time()) . $time;
        $json = file_get_contents("http://www.twototwo.cn/train/Service.aspx?format=json&action=QueryTrainScheduleByTwoStation&key=5da453b2-b154-4ef1-8f36-806ee58580f6&startStation=" . $data[0] . "&arriveStation=" . $data[1] . "&startDate=" . $data[2] . "&ignoreStartDate=0&like=1&more=0");
        if ($json) {
            $data = json_decode($json);
            $main = $data->Response->Main->Item;
            if (count($main) > 10) {
                $conunt = 10;
            } else {
                $conunt = count($main);
            }
            for ($i = 0; $i < $conunt; $i++) {
                $str .= "\n 【编号】" . $main[$i]->CheCiMingCheng . "\n 【类型】" . $main[$i]->CheXingMingCheng . "\n【发车时间】:　" . $time . ' ' . $main[$i]->FaShi . "\n【耗时】" . $main[$i]->LiShi . ' 小时';
                $str .= "\n----------------------";
            }
        } else {
            $str = '没有找到 ' . $name . ' 至 ' . $toname . ' 的列车';
        }
        return $str;
    }
    function fanyi($name)
    {
        $name = array_merge($name);
        $url  = "http://openapi.baidu.com/public/2.0/bmt/translate?client_id=kylV2rmog90fKNbMTuVsL934&q=" . $name[0] . "&from=auto&to=auto";
        $json = Http::fsockopenDownload($url);
        if ($json == false) {
            $json = file_get_contents($url);
        }
        $json = json_decode($json);
        $str  = $json->trans_result;
        if ($str[0]->dst == false)
            return $this->error_msg($name[0]);
        $mp3url = 'http://www.apiwx.com/aaa.php?w=' . $str[0]->dst;
        return array(
            array(
                $str[0]->src,
                $str[0]->dst,
                $mp3url,
                $mp3url
            ),
            'music'
        );
    }
    function caipiao($name)
    {
        $name = array_merge($name);
        $url  = "http://api2.sinaapp.com/search/lottery/?appkey=0020130430&appsecert=fa6095e113cd28fd&reqtype=text&keyword=" . $name[0];
        $json = Http::fsockopenDownload($url);
        if ($json == false) {
            $json = file_get_contents($url);
        }
        $json = json_decode($json, true);
        $str  = $json['text']['content'];
        return $str;
    }
    function mengjian($name)
    {
        $name = array_merge($name);
        if (empty($name))
            return '周公睡着了,无法解此梦,这年头神仙也偷懒';
        $data = M('Dream')->field('content')->where("`title` LIKE '%" . $name[0] . "%'")->find();
        if (empty($data))
            return '周公睡着了,无法解此梦,这年头神仙也偷懒';
        return $data['content'];
    }
    function test($name, $data)
    {
        file_put_contents($name, $data);
    }
    function gupiao($name)
    {
        $name = array_merge($name);
        $url  = "http://api2.sinaapp.com/search/stock/?appkey=0020130430&appsecert=fa6095e113cd28fd&reqtype=text&keyword=" . $name[0];
        $json = Http::fsockopenDownload($url);
        if ($json == false) {
            $json = file_get_contents($url);
        }
        $json = json_decode($json, true);
        $str  = $json['text']['content'];
        return $str;
    }
    function getmp3($data)
    {
        $obj            = new getYu();
        $ContentString  = $obj->getGoogleTTS($data);
        $randfilestring = 'mp3/' . time() . '_' . sprintf('%02d', rand(0, 999)) . ".mp3";
        file_put_contents($randfilestring, $ContentString);
        return rtrim(C('site_url'), '/') . $randfilestring;
    }
    function xiaohua()
    {
        $name = implode('', $n);
        @$str = 'http://api.ajaxsns.com/api.php?key=free&appid=0&msg=' . urlencode('笑话' . $name);
        $json = json_decode(file_get_contents($str));
        $str  = str_replace('{br}', "\n", $json->content);
        return str_replace('mzxing_com', 'weiyundian', $str);
    }
    function liaotian($name)
    {
        $name = array_merge($name);
        $this->chat($name[0]);//从关键词中取第一个作为自动回复的关键词
    }
    function chat($name)
    {
        $this->requestdata('textnum');
        $check = $this->user('connectnum');
        if ($check['connectnum'] != 1) {
            return C('connectout');
        }
        if ($name == "你叫什么" || $name == "你是谁") {
            return '咳咳，我是聪明与智慧并存的美女，主淫你可以叫我' . $this->my . ',人家刚交男朋友,你不可追我啦';
        } elseif ($name == "你父母是谁" || $name == "你爸爸是谁" || $name == "你妈妈是谁") {
            return '主淫,' . $this->my . '是weiyundian创造的,所以他们是我的父母,不过主人我属于你的';
        } elseif ($name == '糗事') {
            $name = '笑话';
        } elseif ($name == '网站' || $name == '官网' || $name == '网址' || $name == '3g网址') {
            return "【Weiyundian官网网址】\n" . C('site_url') . "!";
        }
        return "请回复\"帮助\"获取更多信息";
        //取消机器人聊天，提高响应速度，避免网络问题导致空回复
        /*
        $str  = 'http://api.ajaxsns.com/api.php?key=free&appid=0&msg=' . urlencode($name);
        $json = json_decode(file_get_contents($str));
        $str  = str_replace('菲菲', $this->my, str_replace('提示：', $this->my . '提醒您:', str_replace('{br}', "\n", $json->content)));
        return str_replace('mzxing_com', 'weiyundian', $str);
        */
    }
    public function fistMe($data)
    {
        if ('event' == $data['MsgType'] && 'subscribe' == $data['Event']) {
            return $this->help();
        }
    }
    public function help()
    {
	    $open = M('Token_open')->where(array(
					'token' => $this->_get('token')
				    ))->find();
		Log::write($open['queryname'],Log::INFO);
	    $datafun   = explode(',', $open['queryname']);
		$fun=M('Function')->where(array('status'=>1,'isserve'=>1))->select();
		
		foreach($fun as $k=>$v)
		{
			$function[$v['funname']]=$v['info'];
		}
		$str="";
		$num=0;
		foreach($datafun as $k=>$v)
		{
			if($function[$v] != "")
			{
			$str=$str.$num.".".$function[$v]."\r\n";
			$num++;
			}
		}
       // $data = M('Areply')->where(array(
         //   'token' => $this->token
       // ))->find();
        return array(
            //preg_replace("/(\015\012)|(\015)|(\012)/", "\n", $str),
			$str,
            'text'
        );
    }
    function error_msg($data)
    {
        return '没有找到' . $data . '相关的数据';
    }
    public function user($action, $keyword = '')
    {
        $user      = M('Wxuser')->field('uid')->where(array(
            'token' => $this->token
        ))->find();
        $usersdata = M('Users');
        $dataarray = array(
            'id' => $user['uid']
        );
        $users     = $usersdata->field('gid,diynum,connectnum,activitynum,viptime')->where(array(
            'id' => $user['uid']
        ))->find();
        $group     = M('User_group')->where(array(
            'id' => $users['gid']
        ))->find();
        if ($users['diynum'] < $group['diynum']) {
            $data['diynum'] = 1;
            if ($action == 'diynum') {
                $usersdata->where($dataarray)->setInc('diynum');
            }
        }
        if ($users['connectnum'] < $group['connectnum']) {
            $data['connectnum'] = 1;
            if ($action == 'connectnum') {
                $usersdata->where($dataarray)->setInc('connectnum');
            }
        }
        if ($users['viptime'] > time()) {
            $data['viptime'] = 1;
        }
        return $data;
    }
    public function requestdata($field)
    {
        $data['year']  = date('Y');
        $data['month'] = date('m');
        $data['day']   = date('d');
        $data['token'] = $this->token;
        $Requestdata   = M('Requestdata');
        $check         = $Requestdata->field('id')->where($data)->find();
        if ($check == false) {
            $data['time'] = time();
            $data[$field] = 1;
            $Requestdata->add($data);
        } else {
            $Requestdata->where($data)->setInc($field);
        }
    }
    function baike($name)
    {
        $name = implode('', $name);
        if ($name == 'weiyundian') {
            return '世界上最牛B的微信营销系统，两天前被腾讯收购，当然这只是一个笑话';
        }
        $name_gbk         = iconv('utf-8', 'gbk', $name);
        $encode           = urlencode($name_gbk);
        $url              = 'http://baike.baidu.com/list-php/dispose/searchword.php?word=' . $encode . '&pic=1';
        $get_contents     = $this->httpGetRequest_baike($url);
        $get_contents_gbk = iconv('gbk', 'utf-8', $get_contents);
        preg_match("/URL=(\S+)'>/s", $get_contents_gbk, $out);
        $real_link     = 'http://baike.baidu.com' . $out[1];
        $get_contents2 = $this->httpGetRequest_baike($real_link);
        preg_match('#"Description"\scontent="(.+?)"\s\/\>#is', $get_contents2, $matchresult);
        if (isset($matchresult[1]) && $matchresult[1] != "") {
            return htmlspecialchars_decode($matchresult[1]);
        } else {
            return "抱歉，没有找到与“" . $name . "”相关的百科结果。";
        }
    }
    function httpGetRequest_baike($url)
    {
        $headers = array(
            "User-Agent: Mozilla/5.0 (Windows NT 5.1; rv:14.0) Gecko/20100101 Firefox/14.0.1",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: en-us,en;q=0.5",
            "Referer: http://www.baidu.com/"
        );
        $ch      = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $output = curl_exec($ch);
        curl_close($ch);
        if ($output === FALSE) {
            return "cURL Error: " . curl_error($ch);
        }
        return $output;
    }
    public function get_tags($title, $num = 10)
    {
        vendor('Pscws.Pscws4', '', '.class.php');
        $pscws = new PSCWS4();
        $pscws->set_dict(CONF_PATH . 'etc/dict.utf8.xdb');
        $pscws->set_rule(CONF_PATH . 'etc/rules.utf8.ini');
        $pscws->set_ignore(true);
        $pscws->send_text($title);
        $words = $pscws->get_tops($num);
        $pscws->close();
        $tags = array();
        foreach ($words as $val) {
            $tags[] = $val['word'];
        }
        return implode(',', $tags);
    }
}

?>
