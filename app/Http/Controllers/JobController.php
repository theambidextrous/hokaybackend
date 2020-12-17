<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Validator;
use Storage;
use Config;
use Carbon\Carbon;
use App\Models\Job;
use App\Models\Bot;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Employer;
/** mailables */
use App\Mail\JobPosted;
use App\Mail\Paid;
use App\Mail\PayFailed;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    public function get_bsw_from()
    {
        $rtn = Bot::where('name', 'bsw')->first();
        if( !is_null($rtn) )
        {
            return response([
                'status' => 200,
                'message' => "success",
                'bsw_from' => intval($rtn->b_from),
            ], 200); 
        }
        return response([
            'status' => 200,
            'message' => "something went offffff! ",
            'bsw_from' => 0,
        ], 200); 
    }
    public function update_bsw_from(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'bsw_from' => 'required',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => "Forbidden. Errors occured",
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $curr = Bot::where('name', 'bsw')->first();
        $curr = $curr->b_from + $req->get('bsw_from');
        $rtn = Bot::where('name', 'bsw')->update(['b_from' => $curr]);
        return response([
            'status' => 200,
            'message' => "Done",
        ], 200); 
    }
    public function get_cigna_from()
    {
        $rtn = Bot::where('name', 'cigna')->first();
        if( !is_null($rtn) )
        {
            return response([
                'status' => 200,
                'message' => "success",
                'cigna_from' => intval($rtn->b_from),
            ], 200); 
        }
        return response([
            'status' => 200,
            'message' => "something went offffff! ",
            'cigna_from' => 0,
        ], 200); 
    }
    public function update_cigna_from(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'cigna_from' => 'required',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => "Forbidden. Errors occured",
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $curr = Bot::where('name', 'cigna')->first();
        $curr = $curr->b_from + $req->get('cigna_from');
        $rtn = Bot::where('name', 'cigna')->update(['b_from' => $curr]);
        return response([
            'status' => 200,
            'message' => "Done",
        ], 200); 
    }
    public function add_manual( Request $req)
    {
        $validator = Validator::make($req->all(), [
            'organization' => 'required|string',
            'title' => 'required|string',
            'primary_tag' => 'required|string|not_in:nn',
            'tags' => 'required|string',
            'brief' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'remote' => 'required',
            'link' => 'required|string',
            'source' => 'string',
            'co_mail' => 'required|string',
            'co_twitter' => 'string',
            'howto' => 'required|string',
            'salary' => 'string',
            /** additives */
            'show_logo' => 'string',
            'bump' => 'string',
            'match' => 'string',
            'yellow_it' => 'string',
            'brand_color' => 'string',
            'sticky_day' => 'string',
            'sticky_week' => 'string',
            'sticky_month' => 'string',
            'selected_color' => 'string',
            /** stripe */
            'inv_address' => 'required|string',
            'inv_amount' => 'required',
            'payment_method' => 'required|string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => "The following errors occured:- " . $this->format_err($validator->errors()->all()),
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $edit_url = null;
        try{
            \Stripe\Stripe::setApiKey(Config::get('app.stripe_private_key'));
            $input = $req->all();
            if( intval($input['inv_amount']) <= 0 )
            {
                return response([
                    'status' => 201,
                    'message' => "The following errors occured:- Amount is invalid",
                    'errors' => [],
                ], 403);
            }
            $input['show_logo'] = $this->clean_bool($input['show_logo']);
            $input['bump'] = $this->clean_bool($input['bump']);
            $input['match'] = $this->clean_bool($input['match']);
            $input['yellow_it'] = $this->clean_bool($input['yellow_it']);
            $input['brand_color'] = $this->clean_bool($input['brand_color']);
            $input['sticky_day'] = $this->clean_bool($input['sticky_day']);
            $input['sticky_week'] = $this->clean_bool($input['sticky_week']);
            $input['sticky_month'] = $this->clean_bool($input['sticky_month']);
            $input['remote'] = $this->clean_bool($input['remote']);
            $input['logo'] = 'none';
            if( $req->hasFile('logo') )
            {
                $content = $req->file('logo');
                $extension = $content->getClientOriginalExtension();
                $content_name = (string) Str::uuid() . '.' . $extension;
                if ( !$this->validExt($extension) )
                {
                    return response([
                        'status' => 201,
                        'message' => "only png and jpg images allowed for logo",
                        'errors' => [],
                    ], 403);
                }
                $req->logo->move(base_path('public'), $content_name);
                // $content->store($content_name, ['disk' => 'hokay']);
                $input['logo'] = url($content_name);
            }        
            /** create stripe user */
            $stripe_id = null;
            if( !$this->exists_emp($input) )
            {
                $_stripe_res = $this->stripe_customer([
                    'name' => $input['organization'],
                    'email' => $input['co_mail'],
                    'phone' => null,
                    'description' => 'Employer on HealthcareOkay.com'
                ]);
                if( is_null($_stripe_res->id) ){
                    return response([
                        'status' => 201,
                        'message' => "We could not verify your information on stripe.",
                        'errors' => json_decode($_stripe_res, true),
                    ], 403);
                }
                $stripe_id = $_stripe_res->id;
            }
            /** create h|Ok user */
            $employer_payload = [
                'name' => $input['organization'],
                'email' => $input['co_mail'],
                'stripe_user' => $stripe_id
            ];
            $emp_id = $this->new_employer($employer_payload);
            /** link payment method to customer */
            $p_method = \Stripe\PaymentMethod::retrieve($input['payment_method']);
            $p_method->attach(['customer' => Employer::find($emp_id)->stripe_user ]);
            Employer::find($emp_id)->update([
                'stripe_pay_method' => $input['payment_method'],
                'stripe_crd_object' => $input['payment_method'],
            ]);
            /** create job - with invisible status */
            $input['edit_link'] = 'hok-' . (string) Str::uuid();
            $input['ext_id'] = str_replace('-', '', (string) Str::uuid());
            $input['is_visible'] = false;
            $jobId = Job::create($input)->id;
            /** create job inv  */
            $inv_payload = [
                'employer_id' => $emp_id,
                'job_id' => $jobId,
                'inv_address' => $input['inv_address'],
                'inv_notes' => 'No notes applicable',
                'inv_amount' => intval($input['inv_amount']),
                'paid' => false,
                'is_renewable' => $input['bump'],
                'next_bump' => $this->next_bump_job($input['bump']),
            ];
            $invoice_no = Invoice::create($inv_payload)->id;
            $_paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $this->stripeAmount(intval($input['inv_amount'])),
                'currency' => 'usd',
                'description' => 'Payment for a 1 month job promotion ' . Config::get('app.name'),
                'metadata' => ['order_id' => $invoice_no],
                'customer' => Employer::find($emp_id)->stripe_user,
                'payment_method' => $input['payment_method'],
                'error_on_requires_action' => true,
                'confirm' => true,
                'setup_future_usage' => 'on_session',
            ]);
            $payment_payload = [
                'inv_no' => $invoice_no,
                'paid_amount' => intval($input['inv_amount']),
                'pay_string' => json_encode(json_decode($_paymentIntent, true)),
            ];
            Payment::create($payment_payload);
            $mail_payload = [
                'amount' => intval($input['inv_amount']),
                'receipt' => $invoice_no
            ];
            $edit_url = Config::get('app.front_url') . '/employer/edit/' . $input['edit_link'];
            if( $_paymentIntent->status == 'succeeded')
            {
                Job::find($jobId)->update(['is_visible' => true]);
                Invoice::find($invoice_no)->update(['paid' => true ]);
                Mail::to($input['co_mail'])->send(new JobPosted($edit_url));
                Mail::to($input['co_mail'])->send(new Paid($mail_payload));
                return response([
                    'status' => 200,
                    'message' => "Success. Job posted",
                    'jobId' => $jobId,
                    'edit' => $edit_url,
                ], 200);
            }
            else
            {
                /** do mail of payment fail */
                Mail::to($input['co_mail'])->send(new JobPosted($edit_url));
                Mail::to($input['co_mail'])->send(new PayFailed($mail_payload));
                return response([
                    'status' => 201,
                    'message' => "Payment failed. Job was created but it is invisible. Use the edit link to resubmit payment. Edit link: " . $edit_url,
                    'jobId' => $jobId,
                    'edit' => $edit_url,
                ], 403);
            }
        }catch (\Stripe\Exception\CardException $e) {
            return response([
                'status' => 201,
                'message' => $e->getMessage(),
                'jobId' => null,
                'edit' => null,
            ], 403);
        }catch (Exception $e) {
            return response([
                'status' => 201,
                'message' => $e->getMessage(),
                'jobId' => null,
                'edit' => null,
            ], 403);
        }
    }
    public function edit_manual(Request $req, $edit_link)
    {
        /** get job by edit link */
        $found_job = Job::where('edit_link', $edit_link)->first();
        if(is_null($found_job))
        {
            return response([
                'status' => 201,
                'message' => "The job with that edit link was not found.",
                'errors' => [],
            ], 403);
        }
        $jobId = $found_job->id;
        /** validate payload */
        $validator = Validator::make($req->all(), [
            'organization' => 'required|string',
            'title' => 'required|string',
            'primary_tag' => 'required|string|not_in:nn',
            'tags' => 'required|string',
            'brief' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'remote' => 'required',
            'link' => 'required|string',
            'source' => 'string',
            'co_mail' => 'required|string', //readonly
            'co_twitter' => 'string',
            'howto' => 'required|string',
            'salary' => 'required | string',
            /** additives */
            'show_logo' => 'string',
            'bump' => 'string',
            'match' => 'string',
            'yellow_it' => 'string',
            'brand_color' => 'string',
            'sticky_day' => 'string',
            'sticky_week' => 'string',
            'sticky_month' => 'string',
            'selected_color' => 'string',
            /** stripe */
            'inv_address' => 'string',
            'inv_amount' => 'string',
            'payment_method' => 'string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => "The following errors occured:- " . $this->format_err($validator->errors()->all()),
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        if( $found_job->is_visible )
        {
            $found_job->organization = $req->get('organization');
            $found_job->title = $req->get('title');
            $found_job->primary_tag = $req->get('primary_tag');
            $found_job->tags = $req->get('tags');
            $found_job->brief = $req->get('brief');
            $found_job->description = $req->get('description');
            $found_job->location = $req->get('location');
            $found_job->link = $req->get('link');
            $found_job->co_twitter = $req->get('co_twitter');
            $found_job->howto = $req->get('howto');
            $found_job->salary = $req->get('salary');
            if( $req->hasFile('logo') )
            {
                $content = $req->file('logo');
                $extension = $content->getClientOriginalExtension();
                $content_name = (string) Str::uuid() . '.' . $extension;
                if ( !$this->validExt($extension) )
                {
                    return response([
                        'status' => 201,
                        'message' => "only png and jpg images allowed for logo",
                        'errors' => [],
                    ], 403);
                }
                $req->logo->move(base_path('public'), $content_name);
                $found_job->logo = url($content_name);
            }     
            $found_job->save();
            return response([
                'status' => 200,
                'message' => "Job post was updated successfully",
                'errors' => [],
            ], 200);
        }
        try{
            \Stripe\Stripe::setApiKey(Config::get('app.stripe_private_key'));
            $input = $req->all();
            if( intval($input['inv_amount']) <= 0 )
            {
                return response([
                    'status' => 201,
                    'message' => "The following errors occured:- Amount is invalid",
                    'errors' => [],
                ], 403);
            }
            $input['show_logo'] = $this->clean_bool($input['show_logo']);
            $input['bump'] = $this->clean_bool($input['bump']);
            $input['match'] = $this->clean_bool($input['match']);
            $input['yellow_it'] = $this->clean_bool($input['yellow_it']);
            $input['brand_color'] = $this->clean_bool($input['brand_color']);
            $input['sticky_day'] = $this->clean_bool($input['sticky_day']);
            $input['sticky_week'] = $this->clean_bool($input['sticky_week']);
            $input['sticky_month'] = $this->clean_bool($input['sticky_month']);
            $input['remote'] = $this->clean_bool($input['remote']);
            $input['logo'] = $found_job->logo;
            if( $req->hasFile('logo') )
            {
                $content = $req->file('logo');
                $extension = $content->getClientOriginalExtension();
                $content_name = (string) Str::uuid() . '.' . $extension;
                if ( !$this->validExt($extension) )
                {
                    return response([
                        'status' => 201,
                        'message' => "only png and jpg images allowed for logo",
                        'errors' => [],
                    ], 403);
                }
                $req->logo->move(base_path('public'), $content_name);
                $input['logo'] = url($content_name);
            }        
            /** find emp*/
            $emp_object = Employer::where('email', $input['co_mail'])->first();
            $emp_id = $emp_object->id;
            /** update payment method of customer  -- assume new card*/
            $p_method = \Stripe\PaymentMethod::retrieve($input['payment_method']);
            $p_method->attach(['customer' => $emp_object->stripe_user ]);
            $emp_object->stripe_pay_method = $input['payment_method'];
            $emp_object->stripe_crd_object = $input['payment_method'];
            /** update job - with invisible status */
            $input['is_visible'] = false;
            Job::find($jobId)->update($input);
            /** update job inv  */
            $invoice_no = null;
            $invoice_object = Invoice::where('job_id', $jobId)->where('employer_id', $emp_id)->where('paid', false)->first();
            if(is_null($invoice_object))
            {
                $inv_payload = [
                    'employer_id' => $emp_id,
                    'job_id' => $jobId,
                    'inv_address' => $input['inv_address'],
                    'inv_notes' => 'No notes applicable',
                    'inv_amount' => intval($input['inv_amount']),
                    'paid' => false,
                    'is_renewable' => $input['bump'],
                    'next_bump' => $this->next_bump_job($input['bump']),
                ];
                $invoice_no = Invoice::create($inv_payload)->id;
            }else
            {
                $invoice_object->inv_address = $input['inv_address'];
                $invoice_object->inv_amount = intval($input['inv_amount']);
                $invoice_object->is_renewable = $input['bump'];
                $invoice_object->next_bump = $this->next_bump_job($input['bump']);
                $invoice_object->save();
                $invoice_no = $invoice_object->id;
            }
            $_paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $this->stripeAmount(intval($input['inv_amount'])),
                'currency' => 'usd',
                'description' => 'Payment for a 1 month job promotion ' . Config::get('app.name'),
                'metadata' => ['order_id' => $invoice_no],
                'customer' => $emp_object->stripe_user,
                'payment_method' => $input['payment_method'],
                'error_on_requires_action' => true,
                'confirm' => true,
                'setup_future_usage' => 'on_session',
            ]);
            $payment_payload = [
                'inv_no' => $invoice_no,
                'paid_amount' => intval($input['inv_amount']),
                'pay_string' => json_encode(json_decode($_paymentIntent, true)),
            ];
            Payment::create($payment_payload);
            $mail_payload = [
                'amount' => intval($input['inv_amount']),
                'receipt' => $invoice_no
            ];
            $edit_url = Config::get('app.front_url') . '/employer/edit/' . $found_job->edit_link;
            if( $_paymentIntent->status == 'succeeded')
            {
                Job::find($jobId)->update(['is_visible' => true]);
                Invoice::find($invoice_no)->update(['paid' => true ]);
                Mail::to($input['co_mail'])->send(new JobPosted($edit_url));
                Mail::to($input['co_mail'])->send(new Paid($mail_payload));
                return response([
                    'status' => 200,
                    'message' => "Success. Job updated successfully",
                    'jobId' => $jobId,
                    'edit' => $edit_url,
                ], 200);
            }
            else
            {
                /** do mail of payment fail */
                Mail::to($input['co_mail'])->send(new JobPosted($edit_url));
                Mail::to($input['co_mail'])->send(new PayFailed($mail_payload));
                return response([
                    'status' => 201,
                    'message' => "Payment failed. Job was created but it is invisible. Use the edit link to resubmit payment. Edit link: " . $edit_url,
                    'jobId' => $jobId,
                    'edit' => $edit_url,
                ], 403);
            }
        }catch (\Stripe\Exception\CardException $e) {
            return response([
                'status' => 201,
                'message' => $e->getMessage() . ". You can edit your job by visiting " . $edit_url,
                'jobId' => $jobId,
                'edit' => $edit_url,
            ], 403);
        }catch (Exception $e) {
            return response([
                'status' => 201,
                'message' => $e->getMessage() . ". You can edit your job by visiting " . $edit_url,
                'jobId' => $jobId,
                'edit' => $edit_url,
            ], 403);
        }
    }
    protected function stripeAmount($amt)
    {
        return floor($amt*100);
    }
    protected function next_bump_job($bool)
    {
        $now = date('Y-m-d H:i:s');
        $next_b = date('Y-m-d H:i:s', strtotime($now. ' + 30 days'));
        if($bool)
        {
            return $next_b;
        }
        return null;
    }
    protected function new_employer($data)
    {
        $emp = Employer::where('email', $data['email'])->first();
        if( !is_null($emp) && $emp->email == $data['email'] )
        {
            return $emp->id;
        }
        return Employer::create($data)->id;
    }
    protected function exists_emp($data)
    {
        $emp = Employer::where('email', $data['co_mail'])->count();
        if( $emp )
        {
            return true;
        }
        return false;
    }
    protected function stripe_customer($in)
    {
        $stripe = new \Stripe\StripeClient(Config::get('app.stripe_private_key'));
        $stripe_res = $stripe->customers->create([
            'name' => $in['name'],
            'email' => $in['email'],
            'phone' => $in['phone'],
            'description' => $in['description'],
        ]);
        return $stripe_res;
    }
    protected function clean_bool($in)
    {
        if( $in == 'false' )
        {
            return false;
        }
        return true;
    }
    protected function format_err($err)
    {
        return implode(', ', $err);
    }
    protected function f_py_bool($in)
    {
        if( $in == 'True')
        {
            return true;
        }
        return false;
    }
    public function add(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'title' => 'required|string',
            'brief' => 'required|string',
            'description' => 'required|string',
            'organization' => 'required|string',
            'location' => 'required|string',
            'remote' => 'required',
            'link' => 'required|string',
            'source' => 'string',
            'logo' => 'string',
            'date_posted' => 'string',
            'job_link' => 'string',
            'ext_id' => 'string',
            'created_at' => 'string',
            'primary_tag' => 'string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => "Forbidden. Errors occured",
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $input = $req->all();
        if($input['show_logo'] == 'True')
        {
            $input['show_logo'] = true;
        }
        $input['sticky_week'] = $this->f_py_bool($input['sticky_week']);
        $input['yellow_it'] = $this->f_py_bool($input['yellow_it']);
        $input['tags'] = join(',', $this->extract_tag($input['primary_tag']));
        $input['primary_tag'] = explode(' ', $input['primary_tag'])[0];
        $input['is_visible'] = true;
        $input['edit_link'] = 'hok-' . (string) Str::uuid();
        if( strlen($input['ext_id']) && $this->isDone($input['ext_id']) )
        {
            return response([
                'status' => 200,
                'message' => "Success. Job exists",
                'jobId' => null,
            ], 200);
        }
        $jobId = Job::create($input)->id;
        return response([
            'status' => 200,
            'message' => "Success. Job posted",
            'jobId' => $jobId,
        ], 200);
    }
    protected function extract_tag($string)
    {
        $arr = explode(' ', $string);
        $c = [];
        foreach( $arr as $b ){
            if(strlen($b) > 4)
            {
                array_push($c, $b);
            }
        }
        return $c;
    }
    protected function isDone($ext_id)
    {
        $count = Job::where('ext_id', $ext_id)->where('ext_id', '!=', null)->count();
        if( $count > 0 )
        {
            return true;
        }
        return false;
    }
    public function update(Request $req, $jobId)
    {
        $validator = Validator::make($req->all(), [
            'title' => 'required|string',
            'brief' => 'required|string',
            'description' => 'required|string',
            'organization' => 'required|string',
            'location' => 'required|string',
            'remote' => 'required',
            'link' => 'required|string',
            'source' => 'string',
        ]);
        if( $validator->fails() ){
            return response([
                'status' => 201,
                'message' => "Forbidden. Errors occured",
                'errors' => $validator->errors()->all(),
            ], 403);
        }
        $input = $req->all();
        Job::find($jobId)->update($input);
        return response([
            'status' => 200,
            'message' => "Success. Job updated",
            'jobId' => $jobId,
        ], 200);
    }
    public function findby_editlink($editlink)
    {
        $data = Job::where('edit_link', $editlink)->first();
        if(is_null($data))
        {
            return response([
                'status' => 201,
                'message' => "Success. No Job found",
                'payload' => [],
            ], 403);
        }
        if(!$data->is_visible)
        {
            // $data->show_logo = false;
            $data->bump = false;
            $data->match = false;
            $data->yellow_it = false;
            $data->brand_color = false;
            $data->sticky_day = false;
            $data->sticky_week = false;
            $data->sticky_month = false;
            $data->save();
        }
        return response([
            'status' => 200,
            'message' => "Success. Job found",
            'payload' => $data->toArray(),
        ], 200);
    }
    public function findOne($jobId)
    {
        $data = Job::find($jobId);
        return response([
            'status' => 200,
            'message' => "Success. Job found",
            'payload' => $data,
        ], 200);
    }
    public function by_company($co, $offset)
    {
        $org = join(' ', explode('-', $co));
        $data = Job::where('organization', $org)
            ->orderBy('id', 'desc')
            ->get();
        if( is_null($data) )
        {
            return response([
                'status' => 200,
                'message' => "No Jobs found for " . $org,
                'payload' => [],
                'total_count' => 0,
                'limit' => 50
            ], 200);
        } 
        $data = $data->toArray();
        return response([
            'status' => 200,
            'message' => "Jobs found for " . $org,
            'payload' => $this->filter_visible($data),
            'total_count' => count($data),
            'limit' => 50
        ], 200);
    }
    public function by_tag($tag, $offset)
    {
        $sticky_day = $this->find_sticky_day();
        $sticky_week = $this->find_sticky_week();
        $sticky_month = $this->find_sticky_month();
        $sticky = array_merge($sticky_month, $sticky_week, $sticky_day);
        $sticky_ids = $this->extract_ids($sticky);

        $tag_arr = explode('-', $tag);
        $data = null;
        if(count($tag_arr) > 1 )
        {
            $data = Job::where('id', '>', 0)
            ->where('title', 'like', '%' . strtolower($tag_arr[0]). '%')
            ->whereNotIn('id', $sticky_ids)
            ->orWhere('description', 'like', '%' . strtolower($tag_arr[0]). '%')
            ->orWhere('primary_tag', 'like', '%' . $tag . '%')
            ->orWhere('tags', 'like', '%' . strtolower($tag_arr[0]). '%')
            ->orWhere('description', 'like', '%' . strtolower($tag_arr[1]). '%')
            ->orWhere('tags', 'like', '%' . strtolower($tag_arr[1]). '%')
            ->skip($offset)
            ->take(50)
            ->orderBy('id', 'desc')
            ->get();
        }
        else
        {
            $data = Job::where('id', '>', 0)
            ->where('title', 'like', '%' . strtolower($tag_arr[0]). '%')
            ->whereNotIn('id', $sticky_ids)
            ->orWhere('description', 'like', '%' . strtolower($tag_arr[0]). '%')
            ->orWhere('primary_tag', 'like', '%' . $tag . '%')
            ->orWhere('tags', 'like', '%' . strtolower($tag_arr[0]). '%')
            ->skip($offset)
            ->take(50)
            ->orderBy('id', 'desc')
            ->get();
        }
        if( is_null($data) )
        {
            return response([
                'status' => 200,
                'message' => "No Jobs found for " . str_replace('-', ' ', $tag),
                'payload' => [],
                'total_count' => 0,
                'limit' => 50
            ], 200);
        } 
        $data = $data->toArray();
        $data = array_merge($sticky, $data);
        return response([
            'status' => 200,
            'message' => "Jobs found for " . $tag,
            'payload' => $this->filter_visible($data),
            'total_count' => count($data),
            'limit' => 50
        ], 200);
    }
    protected function find_sticky_day()
    {
        $data = Job::where('sticky_day', true)
            ->where('created_at', '>=', Carbon::parse('-24 hours'))
            ->get();
        if(!is_null($data))
        {
            return $data->toArray();
        }
        return [];
    }
    protected function find_sticky_week()
    {
        $data = Job::where('sticky_week', true)
            ->where('created_at', '>=', Carbon::parse('-7 days'))
            ->get();
        if(!is_null($data))
        {
            return $data->toArray();
        }
        return [];
    }
    protected function find_sticky_month()
    {
        $data = Job::where('sticky_month', true)
            ->where('created_at', '>=', Carbon::parse('-30 days'))
            ->get();
        if(!is_null($data))
        {
            return $data->toArray();
        }
        return [];
    }
    public function findAll($offset)
    {
        $sticky_day = $this->find_sticky_day();
        $sticky_week = $this->find_sticky_week();
        $sticky_month = $this->find_sticky_month();
        $sticky = array_merge($sticky_month, $sticky_week, $sticky_day);
        $sticky_ids = $this->extract_ids($sticky);
        $data = Job::where('id', '>', 0)
            ->whereNotIn('id', $sticky_ids)
            ->where('is_visible', true)
            ->skip($offset)
            ->take(10)
            ->orderBy('id', 'desc')
            ->get();

        if( is_null($data) )
        {
            return response([
                'status' => 200,
                'message' => "Success. No Jobs found",
                'payload' => [],
                'total_count' => 0,
                'limit' => 100
            ], 200);
        } 
        $data = $data->toArray();
        $data = array_merge($sticky, $data);
        return response([
            'status' => 200,
            'message' => "Success. Jobs found",
            'payload' => $data,
            'total_count' => $this->count_all(),
            'limit' => 100
        ], 200);
    }
    public function searchAll($keyword)
    {
        $data = Job::where('title', 'like', '%'.$keyword.'%')
            ->orWhere('brief', 'like', '%'.$keyword.'%')
            ->orWhere('description', 'like', '%'.$keyword.'%')
            ->orWhere('organization', 'like', '%'.$keyword.'%')
            ->orWhere('location', 'like', '%'.$keyword.'%')
            ->orWhere('link', 'like', '%'.$keyword.'%')
            ->orderBy('id', 'desc')->get();
        if( is_null($data) )
        {
            return response([
                'status' => 200,
                'message' => "Success. No Jobs found",
                'payload' => [],
            ], 200);
        } 
        return response([
            'status' => 200,
            'message' => "Success. Jobs found",
            'payload' => $this->filter_visible($data->toArray()),
        ], 200);
    }
    public function delete($jobId)
    {
        //Job::find($jobId)->delete();
        return response([
            'status' => 200,
            'message' => "Success. Job deleted",
            'jobId' => null,
        ], 200);
    }
    protected function filter_visible($data)
    {
        $filtered = [];
        foreach($data as $_data)
        {
            if($_data['is_visible'])
            {
                array_push($filtered, $_data);
            }
        }
        return $filtered;
    }
    protected function extract_ids($sticky)
    {
        $ids = [];
        foreach( $sticky as $s ){
            array_push($ids, $s['id']);
        }
        return $ids;
    }
    protected function count_all()
    {
        return Job::where('id', '>', 0)->count();
    }
    protected function validExt($ext)
    {
        if( in_array($ext, ['png', 'jpg', 'jpeg']) )
        {
            return true;
        }
        return false;
    }
}
