<?php

namespace GingerPlugins\Entities;

use GingerPlugins\Entities\Transaction\Address;

/**
 *
 * @author  Adriaan Meijer
 * @version 1.0    - Initiele opzet
 *
 */
class Transaction
{
    /** @var  int */
    protected $amount;

    /** @var  string */
    protected $currency;

    /** @var  string */
    protected $status;

    /** @var  int */
    protected $order_id;

    /** @var  int */
    protected $order_number;

    /** @var  string */
    protected $language;

    /** @var  string */
    protected $method;

    /** @var  string */
    protected $issuer;

    /** @var  string */
    protected $return_url;

    /** @var  string */
    protected $webhook_url;

    /** @var  string */
    protected $pay_url;

    /** @var  string */
    protected $transaction_id;

    /** @var  int */
    protected $created;

    /** @var  Address */
    protected $billing_address;

    /** @var  Address */
    protected $shipping_address;

    /** @var  string */
    protected $date_of_birth;

    /** @var  string */
    protected $ipv4_address;

    /** @var null|string */
    protected $error = null;

    public function __construct(\stdClass $oObject)
    {
        if (isset($oObject->amount)) {
            $this->SetAmount($oObject->amount);
        }
        if (isset($oObject->currency)) {
            $this->SetCurrency($oObject->currency);
        }
        if (isset($oObject->language)) {
            $this->SetLanguage($oObject->language);
        }
        if (isset($oObject->return_url)) {
            $this->SetReturnUrl($oObject->return_url);
        }
        if (isset($oObject->webhook_url)) {
            $this->SetWebhookUrl($oObject->webhook_url);
        }
        if (isset($oObject->pay_url)) {
            $this->SetPayUrl($oObject->pay_url);
        }
        if (isset($oObject->order_id)) {
            $this->SetOrderId($oObject->order_id);
        }
        if (isset($oObject->order_number)) {
            $this->SetOrderNumber($oObject->order_number);
        }
        if (isset($oObject->method)) {
            $this->SetMethod($oObject->method);
        }
        if (isset($oObject->issuer)) {
            $this->SetIssuer($oObject->issuer);
        }
        if (isset($oObject->date_of_birth)) {
            $this->SetDateofbirth($oObject->date_of_birth);
        }
        if (isset($oObject->ipv4_address)) {
            $this->SetIpv4address($oObject->ipv4_address);
        }

        $this->billing_address = new Address($oObject->billing_address);
        $this->shipping_address = new Address($oObject->shipping_address);

        /** Defaults */
        $this->SetCreated(gmdate('c', time()));
        $this->SetStatus('OPEN');
        $this->SetTransactionId(uniqid());

        if (!empty($oObject->created)) {
            $this->SetCreated($oObject->created);
        }

        if (!empty($oObject->status)) {
            $this->SetStatus($oObject->status);
        }

        if (!empty($oObject->transaction_id)) {
            $this->SetTransactionId($oObject->transaction_id);
        }
    }

    /**
     * Convert this credential object to an array
     * @return array
     */
    public function ToArray()
    {
        return [
            'amount' => $this->GetAmount(),
            'currency' => $this->GetCurrency(),
            'status' => $this->GetStatus(),
            'order_id' => $this->GetOrderId(),
            'order_number' => $this->GetOrderNumber(),
            'language' => $this->GetLanguage(),
            'method' => $this->GetMethod(),
            'issuer' => $this->GetIssuer(),
            'return_url' => $this->GetReturnUrl(),
            'webhook_url' => $this->GetWebhookUrl(),
            'pay_url' => $this->GetPayUrl(),
            'transaction_id' => $this->GetTransactionId(),
            'billing_address' => $this->filterArray((array)$this->GetBillingAddress()),
            'shipping_address' => $this->filterArray((array)$this->GetShippingAddress()),
            'created' => $this->GetCreated(),
            'date_of_birth' => $this->GetDateofbirth(),
            'error' => $this->GetError(),
            'ipv4_address' => $this->GetIpv4address(),

        ];
    }

    public function filterArray($array)
    {
        $new_array = [];
        foreach ($array as $key => $val) {
            $new_array[str_replace('*', '', $key)] = $val;
        }
        return $new_array;
    }

    /**
     * @return \stdClass
     */
    public function toStdClass()
    {
        $oObject = new \stdClass();
        $oObject->status = $this->GetStatus();
        $oObject->pay_url = $this->GetPayUrl();
        $oObject->transaction_id = $this->GetTransactionId();
        $oObject->error = $this->GetError();
        $oObject->amount = $this->GetAmount();
        $oObject->currency = $this->GetCurrency();
        $oObject->return_url = $this->GetReturnUrl();
        $oObject->webhook_url = $this->GetWebhookUrl();
        $oObject->method = $this->GetMethod();
        $oObject->order_id = $this->GetOrderId();
        $oObject->order_number = $this->GetOrderNumber();
        $oObject->language = $this->GetLanguage();
        $oObject->issuer = $this->GetIssuer();

        $oObject->billing_address = $this->GetBillingAddress()->toStdClass();
        $oObject->shipping_address = $this->GetShippingAddress()->toStdClass();

        $oObject->created = $this->GetCreated();
        $oObject->date_of_birth = $this->GetDateofbirth();
        $oObject->ipv4_address = $this->GetIpv4address();

        return $oObject;
    }

    /**
     * @return string
     */
    public function GetTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @return int
     */
    public function GetAmount()
    {
        return $this->amount;
    }

    /**
     * @return string
     */
    public function GetCurrency()
    {
        return $this->currency;
    }

    /**
     * @return string
     */
    public function GetStatus()
    {
        return $this->status;
    }

    /**
     * @return null|string
     */
    public function GetError()
    {
        if (empty($this->error)) {
            switch ($this->GetStatus()) {
                case 'CANCELLED':
                    $this->error = 'The transaction is cancelled by the customer.';
                    break;
                case 'FAILED':
                    $this->error = 'A general error that the transaction can\'t be processed correctly';
                    break;
                case 'EXPIRED':
                    $this->error =
                        'The transaction is expired which can happen if remote services such as iDEAL or PayPal expire the transaction on their side.';
                    break;
                case 'OPEN':
                case 'SUCCESS':
                    break;
            }
        }
        return $this->error;
    }

    /**
     * @return int
     */
    public function GetOrderId()
    {
        return $this->order_id;
    }

    /**
     * @return string
     */
    public function GetOrderNumber()
    {
        return $this->order_number;
    }

    /**
     * @return string
     */
    public function GetLanguage()
    {
        return $this->language;
    }

    /**
     * @return string
     */
    public function GetMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function GetIssuer()
    {
        return $this->issuer;
    }

    /**
     * @return string
     */
    public function GetReturnUrl()
    {
        return $this->return_url;
    }

    /**
     * @return string
     */
    public function GetWebhookUrl()
    {
        return $this->webhook_url;
    }

    /**
     * @return string
     */
    public function GetPayUrl()
    {
        return $this->pay_url;
    }

    /**
     * @return int
     */
    public function GetCreated()
    {
        $sTimeStamp = str_replace('+00:00', 'Z', $this->created);
        return $sTimeStamp;
    }

    /**
     * @return Address
     */
    public function GetBillingAddress()
    {
        return $this->billing_address;
    }

    /**
     * @return Address
     */
    public function GetShippingAddress()
    {
        return $this->shipping_address;
    }

    /**
     * @return string
     */
    public function GetDateofbirth()
    {
        return $this->date_of_birth;
    }

    /**
     * @return string
     */
    public function GetIpv4address()
    {
        return $this->ipv4_address;
    }

    /**
     * @param string $date_of_birth
     *
     * @return \GingerPlugins\Entities\Transaction
     */
    public function SetDateofbirth($date_of_birth)
    {
        $this->date_of_birth = $date_of_birth;
        return $this;
    }

    /**
     * @param string $ipv4_address
     *
     * @return Transaction
     */
    public function SetIpv4address($ipv4_address)
    {
        $this->ipv4_address = $ipv4_address;
        return $this;
    }

    /**
     * @param int $amount
     *
     * @return Transaction
     */
    public function SetAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param string $currency
     *
     * @return Transaction
     */
    public function SetCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @param string $status
     *
     * @return Transaction
     */
    public function SetStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param int $order_id
     *
     * @return Transaction
     */
    public function SetOrderId($order_id)
    {
        $this->order_id = $order_id;
        return $this;
    }

    /**
     * @param string $order_number
     *
     * @return Transaction
     */
    public function SetOrderNumber($order_number)
    {
        $this->order_number = $order_number;
        return $this;
    }

    /**
     * @param string $language
     *
     * @return Transaction
     */
    public function SetLanguage($language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @param string $method
     *
     * @return Transaction
     */
    public function SetMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param string $issuer
     *
     * @return Transaction
     */
    public function SetIssuer($issuer)
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * @param string $return_url
     *
     * @return Transaction
     */
    public function SetReturnUrl($return_url)
    {
        $this->return_url = $return_url;
        return $this;
    }

    /**
     * @param string $webhook_url
     *
     * @return Transaction
     */
    public function SetWebhookUrl($webhook_url)
    {
        $this->webhook_url = $webhook_url;
        return $this;
    }

    /**
     * @param string $pay_url
     *
     * @return Transaction
     */
    public function SetPayUrl($pay_url)
    {
        $this->pay_url = $pay_url;
        return $this;
    }

    /**
     * @param string $transaction_id
     *
     * @return Transaction
     */
    public function SetTransactionId($transaction_id)
    {
        $this->transaction_id = $transaction_id;
        return $this;
    }

    /**
     * @param int $created
     *
     * @return Transaction
     */
    public function SetCreated($created)
    {
        $this->created = $created;
        return $this;
    }

    /**
     * @param Address $billing_address
     *
     * @return Transaction
     */
    public function SetBillingAddress($billing_address)
    {
        $this->billing_address = $billing_address;
        return $this;
    }

    /**
     * @param Address $shipping_address
     *
     * @return Transaction
     */
    public function SetShippingAddress($shipping_address)
    {
        $this->shipping_address = $shipping_address;
        return $this;
    }

    /**
     * @param null|string $error
     *
     * @return Transaction
     */
    public function SetError($error)
    {
        $this->error = $error;
        return $this;
    }
}