## Payment Cycle
For a payment transaction we have to request a payment via web service. If our request is successful the IPG will return a token which we should use while redirecting customer to payment page. Customer will be redirected back to our desired URL(callback URL) from payment page via a POST request carrying data which may be used to check and verify customer's transaction using web service.
## Request payment
For a payment transaction we should send a payment request to IPG and acquire a token. This may be accomplished by calling `getToken` method.
### Instantiating an IPG object
for instantiating an IPG object we should call `Dizatech\BehpardakhtIpg\BehpardakhtIpg` constructor passing it an array of required arguments containing:
* terminalId: your payment gateway terminal id
* userName: your payment gateway username
* userPassword: your payment gateway password
#### Code sample:
```php
$args = [
    'terminalId'    => '123',
    'userName'      => '456',
    'userPassword'  => '789'
]; //Replace arguments with your gateway actual values
$ipg = new BehpardakhtIpg($args);
```
### `getToken` method
#### Arguments:
* order_id: unique order id
* redirect_address: URL to which customer may be redirected after payment
* mobile_no(optional): Customer's mobile phone number in 989********* format
#### Returns:
An object with the following properties:
* status: `success` or `error`
* token: in case of a successful request contains the generated token which may be used while redirecting customer to payment page
* message: contains error message when `status` is `error`
## Redirecting customer to payment page
If `status` property of the result of calling `getToken` is `success` we can redirect customer to payment page URL which is currently `https://bpm.shaparak.ir/pgwchannel/startpay.mellat`. We have to redirect user to payment page via a POST request. So, it is necessary to inject an HTML form in page and submit it as done in the following example. The acquired token should be used as the value of RefId hidden input.

**It is neccessary to save the acquired token token for further use**
#### Code sample:
```php
$args = [
    'terminalId'    => '123',
    'userName'      => '456',
    'userPassword'  => '789'
]; //Replace arguments with your gateway actual values
$ipg = new BehpardakhtIpg($args);
$amount = 1000; //Replace with actual order amount in Rials
$order_id = 1; //Replace it with unique order id
$redirect_address = 'http://my.com/verify'; //Replace with your desired callback page URL
$result = $ipg->getToken($amount, $order_id, $redirect_address);
if( $result->status == 'success' ){
    ?>
    <form
        style="display: none;" id="bp_start_pay"
        action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat" method="post">
        <input type="hidden" name="RefId" value="<?php echo $result->token; ?>">
    </form>
    <script>
        window.onload = function(){
            document.forms['bp_start_pay'].submit();
        }
    </script>
    <?php
    die();
}
else{
    echo "Error: {$result->message}";
}
```
## Payment verification and settle
After payment the customer will be redirected back to the callback URL provided in payment request phase via a POST request carrying all necessary data. Important data fields sent by IPG are:
* RefId: Payment token by which the user has been redirected to payment page
* ResCode: Payment status which should be 0 for successful payments
* SaleOrderId: Unique order id used in payment request phase to acquire payment token
* SaleReferenceId: Reference Id which may be used for further requests and displayed to customer
* FinalAmount: The actual amoun paid by user

**It is important to make sure that the returned RefId matches the token with which we initiated the payment and the FinalAmount matches the amount we hace requested.**

If `ResCode` equals `0`, `RefId` matches original token and `FinalAmount` matches requested amount we can call the `verifyRequest` method to verify payment.
### `verifyRequest` method
#### Arguments:
* order_id: Payment verification request id (no unique constraint). We can use same value for order_id and sale_order_id
* sale_order_id: SaleOrderId returned by gateway
* sale_reference_id: SaleReferenceId returned by gateway
#### Returns:
An object with the following properties:
* status: `success` or `error`
* message: message describing the status
#### Code sample:
```php
$args = [
    'terminalId'    => '123',
    'userName'      => '456',
    'userPassword'  => '789'
]; //Replace arguments with your gateway actual values
$ipg = new BehpardakhtIpg($args);
$result = $ipg->verifyRequest($_POST['SaleOrderId'], $_POST['SaleOrderId'], $_POST['SaleReferenceId']);
```
If we get `success` st  atus we have to continue and settle the transaction via `settleRequest` method.
### `settleRequest` method
#### Arguments:
* order_id: Payment verification request id (no unique constraint). We can use same value for order_id and sale_order_id
* sale_order_id: SaleOrderId returned by gateway
* sale_reference_id: SaleReferenceId returned by gateway
#### Returns:
An object with the following properties:
* status: `success` or `error`
* message: message describing the status
#### Code sample:
```php
$args = [
    'terminalId'    => '123',
    'userName'      => '456',
    'userPassword'  => '789'
]; //Replace arguments with your gateway actual values
$ipg = new BehpardakhtIpg($args);
$result = $ipg->settleRequest($_POST['SaleOrderId'], $_POST['SaleOrderId'], $_POST['SaleReferenceId']);
```
## Reversing Transactions
In case we need to cancel customer order immediately after payment (maximum 3 hours later) we can simply reverse the payment transaction which may result to full and instant refund to customers bank account. For reversing transactions we can call `refundRequest` method.
### `refundRequest` method
#### Arguments:
* order_id: Payment verification request id (no unique constraint). We can use same value for order_id and sale_order_id
* sale_order_id: SaleOrderId returned by gateway
* sale_reference_id: SaleReferenceId returned by gateway
#### Returns:
An object with the following properties:
* status: `success` or `error`
* message: message describing the status
#### Code sample:
```php
$args = [
    'terminalId'    => '123',
    'userName'      => '456',
    'userPassword'  => '789'
]; //Replace arguments with your gateway actual values
$ipg = new BehpardakhtIpg($args);
$result = $ipg->refundRequest($_POST['SaleOrderId'], $_POST['SaleOrderId'], $_POST['SaleReferenceId']);
```
## Important Notes
* `verifyRequest` method only return `success` once for each transaction.
* `settleRequest` method may be called multiple times for any verified transaction and return success every time.
* `refundRequest` method may only be called for settled transactions.