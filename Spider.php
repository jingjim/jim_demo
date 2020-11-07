<?php

namespace common\helpers\spider;

use Doctrine\DBAL\Driver\IBMDB2\DB2Driver;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Html;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
/**
 * Spider 数据爬取类
 * 'cookie: SameSite=none; cna=FzLvF2ChUW4CAXFCI0uJCzzb; xlly_s=1; cookie1=AQJv3IkagpjO2BturAelUdx8FLeSJzcYdlKIrCSlK%2Fk%3D; cookie2=117459837dea632c6e1e1f96a5c343ea; cookie17=UUphw2Qh7h7pt7ojdw%3D%3D; t=d530b466c4fec0502396b87cbc5ed416; _tb_token_=e7e8de977336b; sg=263; csg=e6d01da8; lid=ldz0002; unb=2209109135046; uc4=nk4=0%40DeRz2lE3ECsAPmb2bttQmE81&id4=0%40U2grGNtvcvS9k1yFh4YfjPf5Z43ZFjET; __cn_logon__=true; __cn_logon_id__=ldz0002; ali_apache_track=c_mid=b2b-2209109135046ec454|c_lid=ldz0002|c_ms=1|c_mt=3; ali_apache_tracktmp=c_w_signed=Y; _nk_=ldz0002; last_mid=b2b-2209109135046ec454; _csrf_token=1600773965900; _is_show_loginId_change_block_=b2b-2209109135046ec454_false; _show_force_unbind_div_=b2b-2209109135046ec454_false; _show_sys_unbind_div_=b2b-2209109135046ec454_false; _show_user_unbind_div_=b2b-2209109135046ec454_false; __rn_alert__=false; alicnweb=touch_tb_at%3D1600773969373%7Clastlogonid%3Dldz0002; UM_distinctid=174b59069f0df-0ae353f7bad478-383e570a-13c680-174b59069f1745; taklid=4a614f936b7e4946b0b76b7c41833549; __mbox_csrf_token=1zXnOKLX4ey1B6A7_1600777217124; tfstk=cNC5BF2I30m78-yEaaa2YEH6dQOcZNcWuztcPT_sKnY3EU75iTcwf7QR-mhvBE1..; l=eBrX46Z4OFcv48IvXOfwourza77OSIRAguPzaNbMiOCP9Lfv5RMAWZr1OzYJC3GVhsIJR3PPYo4UBeYBqIVQ732BnUGYorDmn; isg=BDw8WgmqT6b2M3v6s6ilEC0FDdruNeBfWTGkfha9SCcK4dxrPkWw77JXwQmZqRi3'
 */
class Spider
{

    //从1688商品详情中获取规格筛选参数
    public static function detail1688($link){

        $cookie = 'Cookie: PHPSESSID=63rctgfkis1pk00sovaoh3oct4';
        $options = [];
        if($cookie){
            $options[] = $cookie;
        }
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $filename = 'test';

            $styleArray = [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ];
            $sort = 1;
            $excel_key = ['A','B','C','D','F'];
            $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(25);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(25);

            $spreadsheet->getActiveSheet()->getDefaultRowDimension()->setRowHeight(20);

        for ($i=1;$i<40;$i++){
            $url = 'http://doc.we7shop.com/apidoc/detail/id/15/pageid/'.$i;

            $text = Http::get($url, null, $options);

        //        $text = iconv("gbk","utf-8", $text);
                if(preg_match('%<h3>(.*?)<\/h3>%',$text, $m)){
                    $title = $m[1];
                }
                preg_match('|<textarea style="display: none">(.*)</textarea>|isU',$text,$info);
                $data = str_replace(array("\r\n", "\r", "\n"," ","\t","-"),'',$info[1]);
                preg_match('|备注：(.*)|isU',$data,$bz);

                if($data){
                    $data = str_replace('||','|',$data);
                    $data_array =explode("|",$data);
                    $title=$data_array[0].'【'.$title.'】';

                    $sheet->setCellValue('A' . $sort, $title);
                    $spreadsheet->getActiveSheet()->mergeCells('A' . $sort . ':C' . $sort);
                    $sheet->getStyle('A' . $sort)->applyFromArray($styleArray);
                    $sort++;

                    $sheet->setCellValue('A' . $sort, $data_array[1]);
                    $sheet->setCellValue('B' . $sort, $data_array[2]);
                    $sheet->setCellValue('C' . $sort, $data_array[3]);
                    $sort++;

                    array_splice($data_array,0,array_search('id', $data_array));
                    $new_data=[];

                    for($ii=0;$ii<(count($data_array)/3);$ii++){
                       $new_data[] = array_slice($data_array, $ii * 3 ,3);
                    }

                    foreach ($new_data as $newK =>$newV){
                        foreach ($newV as $k =>$v){
                            $sheet->setCellValue($excel_key[$k] . $sort, $v);
                        }
                        $sort++;
                    }
                    $sort++;
                }
        }


        // 清除之前的错误输出
        ob_end_clean();
        ob_start();
        $writer = new Xlsx($spreadsheet);

        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8;");
        header("Content-Disposition: inline;filename=\"{$filename}.xlsx\"");
        header('Cache-Control: max-age=0');
        $writer->save('php://output');

        /* 释放内存 */
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();

        exit();

    }

    //从1688订单列表中获取下游订单对应关系对照对象
    /**
     * 从1688订单列表中获取下游订单对应关系对照对象
     * [
     *    [20200922172434122623] => 1265800861275134650
     *    [20200918125448514759] => 1262684487865134650
     * ]
     */
    public static function orderlist1688($startDate='', $startHour='', $startMinute='', $endDate='', $endHour='', $endMinute='', $page=1){
        $link = "https://trade.1688.com/order/buyer_order_list.htm?scene_type=&source=&product_name=&start_date={$startDate}&start_hour={$startHour}&start_minute={$startMinute}&end_date={$endDate}&end_hour={$endHour}&end_minute={$endMinute}&seller_login_id=&trade_status=&trade_type_search=&biz_type_search=&order_id_search=&is_his=&is_hidden_canceled_offer=&apt=&related_code=&order_settle_flag=&company_name=&keywords=&receiver_tel=&receiver_name=&buyer_name=&down_stream_order_id=&batch_number=&total_fee=&page={$page}";
        
        $key = 'http1688cookie';

        $cache = \Yii::$app->redis;
        $cookie = $cache->get($key);
        
        $options = [];
        if($cookie){
            $options[] = $cookie;
        }
        $text = Http::get($link, null, $options);
        $text = iconv("gbk","utf-8", $text);

        //获取SKU json
        if(preg_match_all('%data-order-id="([0-9]+)"%', $text, $info)){
            $bizOrderList = [];
            foreach($info[1] as $orderId){
                $biz = self::orderdetail1688($orderId);
                if($biz){
                    $order = ['orderid'=>$orderId];
                    if(isset($biz['logisticsid'])){
                        $order['logisticsid'] = $biz['logisticsid'];
                    }
                    if(isset($biz['companyName'])){
                        $order['companyName'] = $biz['companyName'];
                    }
                    if(isset($biz['billno'])){
                        $order['billno'] = $biz['billno'];
                    }
                    $bizOrderList[$biz['orderid']] = $order;
                }
            }
            return $bizOrderList;
        }
        return false;
    }

    //从1688订单详情中获取下游订单号
    public static function orderdetail1688($orderId=''){
        $link = "https://trade.1688.com/order/new_step_order_detail.htm?orderId={$orderId}";
        
        $key = 'http1688cookie';
        $cache = \Yii::$app->redis;
        $cookie = $cache->get($key);
        

        $options = [];
        if($cookie){
            $options[] = $cookie;
        }
        $text = Http::get($link, null, $options);
        $text = iconv("gbk","utf-8", $text);

        $startText = '<li>下游订单号：';
        $endText = '</li>';
        $start = mb_strpos($text, $startText);
        if($start > 0){
            $start = $start + mb_strlen($startText);
            $end = mb_strpos($text, $endText, $start);
            if($end > $start){
                $biz = ['orderid'=>trim(mb_substr($text, $start, $end-$start))];

                $startText = 'data-logisticsid="';
                $endText = '"';
                $start = mb_strpos($text, $startText);
                if($start > 0){
                    $start = $start + mb_strlen($startText);
                    $end = mb_strpos($text, $endText, $start);
                    if($end > $start){
                        $biz['logisticsid'] = trim(mb_substr($text, $start, $end-$start));                        
                    }
                }
                
                $startText = 'data-billno="';
                $endText = '"';
                $start = mb_strpos($text, $startText);
                if($start > 0){
                    $start = $start + mb_strlen($startText);
                    $end = mb_strpos($text, $endText, $start);
                    if($end > $start){
                        $biz['billno'] = trim(mb_substr($text, $start, $end-$start));                        
                    }
                }
                
                $startText = 'data-companyName="';
                $endText = '"';
                $start = mb_strpos($text, $startText);
                if($start > 0){
                    $start = $start + mb_strlen($startText);
                    $end = mb_strpos($text, $endText, $start);
                    if($end > $start){
                        $biz['companyName'] = trim(mb_substr($text, $start, $end-$start));                        
                    }
                }

                return $biz;
            }
        }
        return false;
    }

}