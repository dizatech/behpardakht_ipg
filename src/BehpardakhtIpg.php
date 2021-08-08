<?php

namespace Dizatech\BehpardakhtIpg;

use SoapClient;

class BehpardakhtIpg{
    protected $terminalId;
    protected $userName;
    protected $userPassword;
    protected $wsClient;

    public function __construct($args=[])
    {
        $this->terminalId = $args['terminalId'];
        $this->userName = $args['userName'];
        $this->userPassword = $args['userPassword'];
        $this->wsClient = new SoapClient('https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl');
    }

    public function getToken($amount, $order_id, $redirect_address, $mobile_no=NULL)
    {
        $args = [
            'terminalId'        => $this->terminalId,
            'userName'          => $this->userName,
            'userPassword'      => $this->userPassword,
            'orderId'           => $order_id,
            'amount'            => $amount,
            'localDate'         => date('Ymd'),
            'localTime'         => date('His'),
            'callBackUrl'       => $redirect_address
        ];
        if( !is_null($mobile_no) ){
            $args['mobileNo'] = self::prepareMobileNo($mobile_no);
        }
        
        $result = $this->wsClient->bpPayRequest($args);
        $result = $result->return;
        $result = explode(",", $result);

        $response = new \stdClass();
        if( $result[0] == 0 ){
            $response->status = 'success';
            $response->token = $result[1];
        }
        else{
            $response->status = 'error';
        }
        $response->message = self::getMessage($result[0]);

        return $response;
    }

    public function verifyRequest($order_id, $sale_order_id, $sale_reference_id)
    {
        $args = [
            'terminalId'        => $this->terminalId,
            'userName'          => $this->userName,
            'userPassword'      => $this->userPassword,
            'orderId'           => $order_id,
            'saleOrderId'       => $sale_order_id,
            'saleReferenceId'   => $sale_reference_id
        ];

        $result = $this->wsClient->bpVerifyRequest($args);
        $result = $result->return;

        $response = new \stdClass();
        if( $result == 0 ){
            $response->status = 'success';
        }
        else{
            $response->status = 'error';
        }
        $response->message = self::getMessage($result);

        return $response;
    }

    public function settleRequest($order_id, $sale_order_id, $sale_reference_id)
    {
        $args = [
            'terminalId'        => $this->terminalId,
            'userName'          => $this->userName,
            'userPassword'      => $this->userPassword,
            'orderId'           => $order_id,
            'saleOrderId'       => $sale_order_id,
            'saleReferenceId'   => $sale_reference_id
        ];

        $result = $this->wsClient->bpVerifyRequest($args);
        $result = $result->return;

        $response = new \stdClass();
        if( $result == 0 || $result == 43 || $result == 45 ){
            $response->status = 'success';
        }
        else{
            $response->status = 'error';
        }
        $response->message = self::getMessage($result);

        return $response;
    }

    public function refundRequest($order_id, $sale_order_id, $sale_reference_id)
    {
        $args = [
            'terminalId'        => $this->terminalId,
            'userName'          => $this->userName,
            'userPassword'      => $this->userPassword,
            'orderId'           => $order_id,
            'saleOrderId'       => $sale_order_id,
            'saleReferenceId'   => $sale_reference_id
        ];

        $result = $this->wsClient->bpReversalRequest($args);
        $result = $result->return;

        $response = new \stdClass();
        if( $result == 0 ){
            $response->status = 'success';
        }
        else{
            $response->status = 'error';
        }
        $response->message = self::getMessage($result);

        return $response;
    }

    public static function getMessage($status_code)
    {
        $messages = [
            '0'     => 'تراکنش با موفقیت انجام شد',
            '11'    => 'شماره کارت نامعتبر است',
            '12'    => 'موجودی کافی نیست',
            '13'    => 'رمز نادرست است',
            '14'    => 'تعداد دفعات وارد کردن رمز بیش از حد مجاز است',
            '15'    => 'کارت نامعتبر است',
            '16'    => 'دفعات برداشت وجه بیش از حد مجاز است',
            '17'    => 'کاربر از انجام تراکنش منصرف شده است',
            '18'    => 'تاریخ انقضای کارت گذشته است',
            '19'    => 'مبلغ برداشت وجه بیش از حد مجاز است',
            '111'   => 'صادرکننده کارت نامعتبر است',
            '112'   => 'خطای سوئیچ صادرکننده کارت',
            '113'   => 'پاسخی از صادرکننده کارت دریافت نشد',
            '114'   => 'دارنده کارت مجاز به انجام این تراکنش نیست',
            '21'    => 'پذیرنده نامعتبر است',
            '23'    => 'خطای امنیتی رخ داده است',
            '24'    => 'اطلاعات کاربری پذیرنده نامعتبر است',
            '25'    => 'مبلغ نامعتبر است',
            '31'    => 'پاسخ نامعتبر است',
            '32'    => 'فرمت اطلاعات وارد شده صحیح نیست',
            '33'    => 'حساب نامعتبر است',
            '34'    => 'خطای سیستمی',
            '35'    => 'تاریخ نامعتبر است',
            '41'    => 'شماره درخواست تکراری است',
            '42'    => 'تراکنش فروش یافت نشد',
            '43'    => 'تراکنش قبلا تایید شده است',
            '44'    => 'درخواست تایید یافت نشد',
            '45'    => 'تراکنش نهایی شده است',
            '46'    => 'تراکنش نهایی نشده است',
            '47'    => 'درخواست نهایی کردن تراکنش یافت نشد',
            '48'    => 'تراکنش برگشت خورده است',
            '421'   => 'IP نامعتبر است',
            '51'    => 'تراکنش تکراری است',
            '54'    => 'تراکنش مرجع موجود تیست',
            '55'    => 'تراکنش نامعتبر است',
            '61'    => 'خطا در واریز',
            '62'    => 'مسیر بازگشت به سایت در دامنه ثبت شده برای پذیرنده قرار ندارد',
            '98'    => 'سقف استفاده از رمز ایستا به پایان رسیده است'
        ];

        return isset( $messages[$status_code] ) ? $messages[$status_code] : "نامشخص";
    }

    public static function prepareMobileNo($mobile_no)
    {
        $arabic = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'); //Arabic Numeric
        $persian = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'); //Persian Numeric
        $mobile_no = str_replace($arabic, $mobile_no, $mobile_no);
        $mobile_no = str_replace($persian, $mobile_no, $mobile_no);
        $mobile_no = strrev($mobile_no);
        $mobile_no = substr($mobile_no, 0, 9);
        $mobile_no = strrev($mobile_no);
        $mobile_no = '98' . $mobile_no;
        
        return $mobile_no;
    }
}