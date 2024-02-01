<?php

namespace App\Http\Controllers\Api;

use App\Address;
use App\Auction;
use App\AuctionBid;
use App\Course;
use App\Http\Controllers\Controller;
use App\Invoice;
use App\InvoiceAddress;
use App\MasterLocation;
use App\Transaction;
use App\TransactionDetails;
use Illuminate\Http\Request;
use Validator;

class AuctionBidController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'auction_id'    => 'required|exists:auctions,id',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $auction_bid = AuctionBid::with(['auction', 'user'])->where('auction_id', $request->auction_id)->orderBy('bid', 'DESC')->paginate(10);

        $data = [
            'status'  => true,
            'message' => 'list penawaran',
            'code' => 200,
            'result' => $auction_bid
        ];
        return response()->json($data, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'auction_id'    => 'required|exists:auctions,id',
            'bid'           => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $data = [
                'status'  => false,
                'message' => $validator->errors()->first(),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // init
        $auction_bid = null;

        // CHECK product
        $auction = Auction::find($request->auction_id);

        // if ($product->IsAuction != 'y') { // check product if not type auction
        //     $data = [
        //         'status'  => false,
        //         'message' => 'product invalid, bukan product untuk dilelang',
        //         'code' => 400
        //     ];
        //     return response()->json($data, 400);
        // }

        // check expired auction
        if ($auction->extra_time == null && $auction->end_period <= date('Y-m-d H:i:s')) {
            $data = [
                'status'  => false,
                'message' => 'Lelang sudah berakhir',
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        if ($auction->is_done == 1) {
            $data = [
                'status'  => false,
                'message' => 'Lelang sudah berakhir',
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // check address user
        $address = Address::where('user_id', auth()->user()->id)->first();

        if (!$address) {
            $data = [
                'status'  => false,
                'message' => 'Mohon untuk melengkapi data alamat anda sebelum melakukan bid',
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        $check_last_bid = AuctionBid::where('auction_id', $request->auction_id)->orderBy('bid', 'DESC')->first();

        // check first bid
        if ($request->bid < $auction->open_bid) {
            $data = [
                'status'  => false,
                'message' => 'bid kurang dari harga penawaran awal yaitu ' . rupiah($auction->open_bid),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // check if bid sama dengan bid sebelumnya
        if ($check_last_bid && $request->bid <= $check_last_bid->bid) {
            $data = [
                'status'  => false,
                'message' => 'penawaran harga sama dengan penawaran tertinggi sebelumnya yaitu ' . rupiah($check_last_bid->bid),
                'code' => 400
            ];
            return response()->json($data, 400);
        }

        // check expired auction with extra time
        if ($auction->extra_time) {

            if ($check_last_bid) { // check end time dari bid terakhir
                if ($check_last_bid->end_time <= date('Y-m-d H:i:s')) {
                    $data = [
                        'status'  => false,
                        'message' => 'Lelang sudah berakhir',
                        'code' => 400
                    ];
                    return response()->json($data, 400);
                }
            } else {
                $expired = date('Y-m-d H:i:s', strtotime($auction->end_period . ' +'.$auction->extra_time . 'minute'));
    
                if ($expired <= date('Y-m-d H:i:s')) {
                    $data = [
                        'status'  => false,
                        'message' => 'Lelang sudah berakhir',
                        'code' => 400
                    ];
                    return response()->json($data, 400);
                }
            }
        }
        // dd('a');

        // check jika bid ada deal option nya sama
        if ($auction->deal_option && $request->bid >= $auction->deal_option && ($request->bid % $auction->min_increase_bid) == 0) {
            $end_time = date('Y-m-d H:i:s', strtotime($auction->end_period));
            if ($auction->extra_time) {
                $end_time = date('Y-m-d H:i:s', strtotime($auction->end_period . ' +'.$auction->extra_time . 'minute'));
            }
            $auction_bid = AuctionBid::create([
                'user_id' => auth()->user()->id,
                'auction_id' => $auction->id,
                'bid' => $request->bid,
                'bid_time' => date('Y-m-d H:i:s'),
                'end_time' => $end_time,
            ]);

            // input transaction
            // Initialize
            $bank = 'BCA';
            $no_rek = '54535354';
            $uniqueCode = $this->checkUniqueCode();
            $total_payment = $auction_bid->bid + $uniqueCode;
            $invoice = Invoice::create([
                'user_id'                => $auction_bid->user_id,
                'total_payment'          => $total_payment, 
                'total_payment_original' => $auction_bid->bid,
                'payment_type'           => 1,
                'bank_name'              => $bank,
                'no_rek'                 => $no_rek,
                'unique_code'            => $uniqueCode,
                'status'                 => 0,
                'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
                'invoice_type'           => 0
            ]);


            if ($invoice) {

                // Check Master Location
                $masterLocation = MasterLocation::where('id', $address->district_id)->first();

                $invoiceAddress = InvoiceAddress::create([
                    'invoice_id'        => $invoice->id,
                    'address_id'        => $address->id,
                    'province'          => $masterLocation->provinsi,
                    'city'              => $masterLocation->kota,
                    'district'          => $masterLocation->kecamatan,
                    'address_type'      => $masterLocation->type,
                    'details_address'   => $address->details_address
                ]);



                $transaction = Transaction::create([
                    'store_id'              => $auction->company_id,
                    'invoice_id'            => $invoice->id,
                    'total_payment'         => $total_payment,
                    // 'expedition'            => ($store['expedition']) ? $store['expedition']['expedition'] : null,
                    // 'service'               => ($store['expedition']) ? $store['expedition']['service'] : null,
                    // 'service_description'   => ($store['expedition']) ? $store['expedition']['service_description'] : null,
                    // 'shipping_cost'         => ($store['expedition']) ? $store['expedition']['shipping_cost'] : null,
                    // 'etd'                   => ($store['expedition']) ? $store['expedition']['etd'] : null,
                    'service_date'          => null
                ]);

                $product = Course::find($auction->product_id);

                $transactionDetails = TransactionDetails::create([
                    'transaction_id'              => $transaction->id,
                    'course_id'                   => $product->id,
                    'course_name'                 => $product->name,
                    'course_detail'               => $product->description,
                    'thumbnail'                   => $product->thumbnail,
                    'thumbnail_path'              => $product->thumbnail_path,
                    'price_course'                => $product->price_num,
                    'discount'                    => $product->discount,
                    'slug'                        => $product->slug,
                    'course_package_category'     => $product->course_package_category,
                    'period_day'                  => $product->period_day,
                    'start_time_min'              => $product->start_time_min,
                    'end_time_min'                => $product->end_time_min,
                    'back_payment_status'         => $product->back_payment_status,
                    'is_immovable_object'         => $product->is_immovable_object,
                    'course_category'             => ($product->courseCategory) ? $product->courseCategory->category_id : null,
                    'price_course_after_discount' => ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : 0,
                    'qty'                         => 1,
                    'weight'                      => $product->weight,
                    'back_payment_status'         => $product->back_payment_status,
                    'category_detail_inputs'      => null,
                    'service_date'                => null
                ]);


                $auction->update(['is_done' => 1]);

                $data = [
                    'status'  => true,
                    'message' => 'melakukan penawaran berhasil dengan jumlah langsung deal tampa lelang',
                    'code' => 201,
                    'result' => $auction_bid
                ];
                return response()->json($data, 201);
            }
        }

        if (!$check_last_bid && ($request->bid % $auction->min_increase_bid) == 0 && $request->bid >= $auction->min_increase_bid) { // last bid not exists (first bid)
            $end_time = date('Y-m-d H:i:s', strtotime($auction->end_period));
            if ($auction->extra_time) {
                $end_time = date('Y-m-d H:i:s', strtotime($auction->end_period . ' +'.$auction->extra_time . 'minute'));
            }
            $auction_bid = AuctionBid::create([
                'user_id' => auth()->user()->id,
                'auction_id' => $auction->id,
                'bid' => $request->bid,
                'bid_time' => date('Y-m-d H:i:s'),
                'end_time' => $end_time,
            ]);

            // $data_transfrom = (new AuctionTransformer)->transform($auction);

            // $toFirbase = $this->database->getReference(env('FIREBASE_AUCTION_REFERENCE'))->getChild($product->ID)->getChild($auction->ID)->set($data_transfrom);
        }

        if ($check_last_bid && $request->bid > $check_last_bid->bid && ($request->bid % $auction->min_increase_bid) == 0) { // check higher last bid
            $end_time = date('Y-m-d H:i:s', strtotime($check_last_bid->end_time));
            if ($check_last_bid->end_time >= date('Y-m-d H:i:s')) {
                $end_time = date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +'.$auction->extra_time . 'minute'));
            }
            $auction_bid = AuctionBid::create([
                'user_id' => auth()->user()->id,
                'auction_id' => $auction->id,
                'bid' => $request->bid,
                'bid_time' => date('Y-m-d H:i:s'),
                'end_time' => $end_time,
            ]);

            // $data_transfrom = (new AuctionTransformer)->transform($auction);

            // $toFirbase = $this->database->getReference(env('FIREBASE_AUCTION_REFERENCE'))->getChild($product->ID)->getChild($auction->ID)->set($data_transfrom);
        }

        if ($auction_bid) {
            $data = [
                'status'  => true,
                'message' => 'melakukan penawaran berhasil',
                'code' => 201,
                'result' => $auction_bid
            ];
            return response()->json($data, 201);
        }

        $data = [
            'status'  => false,
            'message' => 'penawaran yang dimasukan tidak sesuai dengan minimun kelipatan bid yaitu ' . rupiah($auction->min_increase_bid),
            'code' => 400
        ];
        return response()->json($data, 400);
    }



    // store to transaction when period product auction expired
    // TODO: cornjob, payment(bank), cost(ongkir), sub_disctrict
    public function expiredAuctionTransaction(Request $request)
    {
        // get all product auction with no have transaction & expired period
        // $product_auction = Product::doesntHave('transactionItem')->where('IsAuction', 'y')->whereNotNull('EndPeriod')->whereDate(\DB::raw('DATE(FROM_UNIXTIME(`EndPeriod`))'), '<=', date('Y-m-d'))->get();
        $product_auction = Auction::whereNull('extra_time')->where('is_done', 0)->whereNull('deleted_at')->where('end_period', '<=', date('Y-m-d H:i:s'))->get();

        $success = array();

        if (count($product_auction) > 0) { // check product auction expired exists
            foreach ($product_auction as $key => $value) {

                // check auction
                $auction_bid = AuctionBid::where('auction_id', $value->id)->orderBy('bid', 'DESC')->first();
                $address = Address::where('user_id', $auction_bid->user_id)->first();

                if ($auction_bid && $address) {
                    // TRANSACTION

                    // Initialize
                    $bank = 'BCA';
                    $no_rek = '54535354';
                    $uniqueCode = $this->checkUniqueCode();
                    $total_payment = $auction_bid->bid + $uniqueCode;
                    $invoice = Invoice::create([
                        'user_id'                => $auction_bid->user_id,
                        'total_payment'          => $total_payment, 
                        'total_payment_original' => $auction_bid->bid,
                        'payment_type'           => 1,
                        'bank_name'              => $bank,
                        'no_rek'                 => $no_rek,
                        'unique_code'            => $uniqueCode,
                        'status'                 => 0,
                        'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
                        'invoice_type'           => 0
                    ]);


                    if ($invoice) {

                        // Check Master Location
                        $masterLocation = MasterLocation::where('id', $address->district_id)->first();

                        $invoiceAddress = InvoiceAddress::create([
                            'invoice_id'        => $invoice->id,
                            'address_id'        => $address->id,
                            'province'          => $masterLocation->provinsi,
                            'city'              => $masterLocation->kota,
                            'district'          => $masterLocation->kecamatan,
                            'address_type'      => $masterLocation->type,
                            'details_address'   => $address->details_address
                        ]);



                        $transaction = Transaction::create([
                            'store_id'              => $value->company_id,
                            'invoice_id'            => $invoice->id,
                            'total_payment'         => $total_payment,
                            // 'expedition'            => ($store['expedition']) ? $store['expedition']['expedition'] : null,
                            // 'service'               => ($store['expedition']) ? $store['expedition']['service'] : null,
                            // 'service_description'   => ($store['expedition']) ? $store['expedition']['service_description'] : null,
                            // 'shipping_cost'         => ($store['expedition']) ? $store['expedition']['shipping_cost'] : null,
                            // 'etd'                   => ($store['expedition']) ? $store['expedition']['etd'] : null,
                            'service_date'          => null
                        ]);

                        $product = Course::find($value->product_id);

                        $transactionDetails = TransactionDetails::create([
                            'transaction_id'              => $transaction->id,
                            'course_id'                   => $product->id,
                            'course_name'                 => $product->name,
                            'course_detail'               => $product->description,
                            'thumbnail'                   => $product->thumbnail,
                            'thumbnail_path'              => $product->thumbnail_path,
                            'price_course'                => $product->price_num,
                            'discount'                    => $product->discount,
                            'slug'                        => $product->slug,
                            'course_package_category'     => $product->course_package_category,
                            'period_day'                  => $product->period_day,
                            'start_time_min'              => $product->start_time_min,
                            'end_time_min'                => $product->end_time_min,
                            'back_payment_status'         => $product->back_payment_status,
                            'is_immovable_object'         => $product->is_immovable_object,
                            'course_category'             => ($product->courseCategory) ? $product->courseCategory->category_id : null,
                            'price_course_after_discount' => ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : 0,
                            'qty'                         => 1,
                            'weight'                      => $product->weight,
                            'back_payment_status'         => $product->back_payment_status,
                            'category_detail_inputs'      => null,
                            'service_date'                => null
                        ]);
                    }

                    $success[] = $value;
                }
                


                $value->update(['is_done' => 1]);
            }
        }

        // untuk auction yg ada extra time
        $product_auction_extra = Auction::whereNotNull('extra_time')->where('is_done', 0)->whereNull('deleted_at')->where('end_period', '<=', date('Y-m-d H:i:s'))->get();
        if (count($product_auction_extra) > 0) { // check product auction expired exists
            foreach ($product_auction_extra as $key => $value) {

                $check_last_bid = AuctionBid::where('auction_id', $value->id)->orderBy('bid', 'DESC')->first();
        
                if ($check_last_bid) { // check end time dari bid terakhir
                    if ($check_last_bid->end_time <= date('Y-m-d H:i:s')) {
                        // check auction
                        $auction_bid = AuctionBid::where('auction_id', $value->id)->orderBy('bid', 'DESC')->first();
                        $address = Address::where('user_id', $auction_bid->user_id)->first();
    
                        if ($auction_bid && $address) {
                            // TRANSACTION
    
                            // Initialize
                            $bank = 'BCA';
                            $no_rek = '54535354';
                            $uniqueCode = $this->checkUniqueCode();
                            $total_payment = $auction_bid->bid + $uniqueCode;
                            $invoice = Invoice::create([
                                'user_id'                => $auction_bid->user_id,
                                'total_payment'          => $total_payment, 
                                'total_payment_original' => $auction_bid->bid,
                                'payment_type'           => 1,
                                'bank_name'              => $bank,
                                'no_rek'                 => $no_rek,
                                'unique_code'            => $uniqueCode,
                                'status'                 => 0,
                                'expired_transaction'    => date('Y-m-d H:i:s', strtotime('+22 hourse')),
                                'invoice_type'           => 0
                            ]);
    
    
                            if ($invoice) {
    
                                // Check Master Location
                                $masterLocation = MasterLocation::where('id', $address->district_id)->first();
    
                                $invoiceAddress = InvoiceAddress::create([
                                    'invoice_id'        => $invoice->id,
                                    'address_id'        => $address->id,
                                    'province'          => $masterLocation->provinsi,
                                    'city'              => $masterLocation->kota,
                                    'district'          => $masterLocation->kecamatan,
                                    'address_type'      => $masterLocation->type,
                                    'details_address'   => $address->details_address
                                ]);
    
    
    
                                $transaction = Transaction::create([
                                    'store_id'              => $value->company_id,
                                    'invoice_id'            => $invoice->id,
                                    'total_payment'         => $total_payment,
                                    // 'expedition'            => ($store['expedition']) ? $store['expedition']['expedition'] : null,
                                    // 'service'               => ($store['expedition']) ? $store['expedition']['service'] : null,
                                    // 'service_description'   => ($store['expedition']) ? $store['expedition']['service_description'] : null,
                                    // 'shipping_cost'         => ($store['expedition']) ? $store['expedition']['shipping_cost'] : null,
                                    // 'etd'                   => ($store['expedition']) ? $store['expedition']['etd'] : null,
                                    'service_date'          => null
                                ]);
    
                                $product = Course::find($value->product_id);
    
                                $transactionDetails = TransactionDetails::create([
                                    'transaction_id'              => $transaction->id,
                                    'course_id'                   => $product->id,
                                    'course_name'                 => $product->name,
                                    'course_detail'               => $product->description,
                                    'thumbnail'                   => $product->thumbnail,
                                    'thumbnail_path'              => $product->thumbnail_path,
                                    'price_course'                => $product->price_num,
                                    'discount'                    => $product->discount,
                                    'slug'                        => $product->slug,
                                    'course_package_category'     => $product->course_package_category,
                                    'period_day'                  => $product->period_day,
                                    'start_time_min'              => $product->start_time_min,
                                    'end_time_min'                => $product->end_time_min,
                                    'back_payment_status'         => $product->back_payment_status,
                                    'is_immovable_object'         => $product->is_immovable_object,
                                    'course_category'             => ($product->courseCategory) ? $product->courseCategory->category_id : null,
                                    'price_course_after_discount' => ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : 0,
                                    'qty'                         => 1,
                                    'weight'                      => $product->weight,
                                    'back_payment_status'         => $product->back_payment_status,
                                    'category_detail_inputs'      => null,
                                    'service_date'                => null
                                ]);
                            }
    
                            $success[] = $value;
                        }

                        $value->update(['is_done' => 1]);
                    }
                }
            }
        }

        if (count($success) > 0) {
            return 'success';
        }

        return 'no action';

    }

    function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function checkUniqueCode()
    {
        // Initialize
        $uniqueCode = rand(100, 1000);
        $nowDate    = date('Y-m-d H:i:s');

        // Check Exists Unique Code
        $uniqueCodeExists = Invoice::where([
                            'unique_code' => $uniqueCode,
                            'status'      => 0
                        ])
                        ->whereDate('expired_transaction', '>=', $nowDate)
                        ->first();

        if ($uniqueCodeExists) {
            for ($i = 0; $i < 100; $i++) { 
                // Initialize
                $uniqueCode       = rand(100, 1000);
                $uniqueCodeExists = Invoice::where([
                                        'unique_code' => $uniqueCode,
                                        'status'      => 0
                                    ])
                                    ->whereDate('expired_transaction', '>=', $nowDate)
                                    ->first();

                if (!$uniqueCodeExists) {
                    break;
                }
            }
        }

        return $uniqueCode;
    }
}
