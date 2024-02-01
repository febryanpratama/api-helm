<?php

namespace App\Http\Controllers\Api\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Course;
use App\Address;
use App\AgreementLetter;
use App\CategoryTransactionAutocomplete;

class CheckSingleCheckoutController extends Controller
{
    public function index()
    {
        // Initialize
        $product = Course::where('id', request('course_id'))->first();

        if (!$product) {
            return response()->json([
                'status'    => 'error',
                'message'   => 'Produk dengan ID ('.request('course_id').') tidak ditemukan.'
            ]);
        }

        // Initialize
        $store              = [];
        $row['store_id']    = $product->user->company_id;
        $row['store_name']  = $product->user->company->Name;

            // Product
            $item['id']                         = 1;
            $item['store_id']                   = $product->user->company_id;
            $item['course_id']                  = $product->id;
            $item['qty']                        = 1;
            $item['course_name']                = $product->name;
            $item['course_description']         = $product->description;
            $item['course_thumbnail']           = $product->thumbnail;
            $item['course_periode_type']        = $product->periode_type;
            $item['course_periode']             = $product->periode;
            $item['course_price']               = $product->price;
            $item['course_price_num']           = $product->price_num;
            $item['course_discount']            = $product->discount;
            $item['course_price_after_disc']    = ($product->discount > 0) ? discountFormula($product->discount, $product->price_num) : 0;
            $item['course_commission']          = $product->commission;
            $item['course_slug']                = $product->slug;
            $item['course_is_publish']          = ($product->is_publish) ? true : false;
            $item['course_termin']              = $product->is_termin;
            $item['weight']                     = $product->weight;
            $item['course_package_category']    = courseCategory($product->course_package_category);
            $item['course_package_category_id'] = $product->course_package_category;
            $item['is_sp']                      = $product->is_sp;
            $item['sp_file']                    = $product->sp_file;
            $item['stock']                      = $product->user_quota_join;
            $item['unit_id']                    = $product->unit_id;
            $item['periode_day']                = $product->period_day;
            $item['unit_name']                  = ($product->unit_id != null) ? $product->unit->name : null;
            $item['store']                      = $this->_store($product);
            $item['course_details']             = $product;
            $item['is_immovable_object']        = $product->is_immovable_object;

            // Category Inputs
            $categoryInputs     = ($product->courseCategory) ? ($product->courseCategory->category) ? $product->courseCategory->category->categoryInputs : null : null;
            $dataCinputs        = [];
            $categoryInputsJson = null;

            if ($categoryInputs) {
                foreach($categoryInputs as $cInputs) {
                    // Initialize
                    $cInput['id']           = $cInputs->id;
                    $cInput['category_id']  = $cInputs->category_id;
                    $cInput['label']        = $cInputs->label;
                    $cInput['field_name']   = $cInputs->field_name;

                    if ($categoryInputsJson) {
                        foreach($categoryInputsJson as $cij) {
                            if ($cInputs->id == $cij['id']) {
                                $cInput['value'] = $cij['value'];

                                break;
                            }
                        }
                    } else {
                        $cInput['value'] = '';
                    }

                    $dataCinputs[] = $cInput;
                }
            }

            $item['category_input'] = $dataCinputs;

            // Check SP Submission
            $submissionSP = null;
            if (auth()->user()) {
                $submissionSP = AgreementLetter::where(['user_id' => auth()->user()->id, 'course_id' => $product->id])->first();
            }

            if (!$submissionSP) {
                $item['submission_sp'] = 0;
            } else {
                $item['submission_sp'] = 1;
            }
            
            // Termin
            $item['is_termin']              = $product->is_termin;
            $item['course_termin_detail']   = $product->courseTermin;

            if ($product->courseTermin) {
                if ($product->courseTermin->is_percentage) {
                    $formula     = ($product->courseTermin->down_payment/100) * $product->courseTermin->installment_amount;
                    $downPayment = ($formula * 1);
                } else {
                    $downPayment = ($product->courseTermin->down_payment * 1);
                }
            } else {
                $downPayment = null;
            }

            $item['course_termin_schedule'] = [
                'down_payment'  => $downPayment,
                'interest'      => ($product->courseTermin) ? $product->courseTermin->interest : null,
                'first_termin'  => ($product->courseTermin) ? ($product->courseTermin->value[0]/100) * $product->courseTermin->installment_amount : null
            ];

            // Initialize
            $customDocumentInput         = [];
            $customDocumentInputRequired = [];

            if ($product->customDocumentInput) {
                foreach ($product->customDocumentInput as $key => $valCDI) {
                    // Initialize
                    $values = json_decode($valCDI->value, true);

                    foreach ($values as $cdiManage) {
                        array_push($customDocumentInput, $cdiManage['name']);
                        array_push($customDocumentInputRequired, $cdiManage['is_required']);
                    }
                }
            }

            // Check if the category has details that should be added 
            if ($product->courseCategory) {
                $categoryTransactionAutocomplete = CategoryTransactionAutocomplete::where('category_id', $product->courseCategory->category_id)->first();
            } else {
                $categoryTransactionAutocomplete = null;
            }

            $item['is_question_required']           = ($categoryTransactionAutocomplete) ? true : false;
            $item['custom_document_input']          = $customDocumentInput;
            $item['custom_document_input_required'] = $customDocumentInputRequired;
            $item['create_at']                      = date('Y-m-d H:i:s');
            $item['update_at']                      = date('Y-m-d H:i:s');

        $store[]        = $item;
        $row['product'] = $store;

        $data[]  = $row;

        return response()->json([
            'status'    => 'success',
            'message'   => 'Data berhasil didapatkan',
            'data'      => $data
        ]);
    }

    private function _store($data)
    {
        // Initialize
        $store   = $data->user->company;
        $address = Address::with('masterLocation')->where('company_id', $data->user->company_id)->first();

        $row['store_id'] = $store->ID;
        $row['name']     = $store->Name;
        $row['phone']    = $store->Phone;
        $row['address']  = $store->Address;
        $row['email']    = $store->Email;
        $row['logo']     = $store->Logo;

        if ($address) {
            $row['address'] = $address;
        }

        return $row;
    }
}
