<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Jobs\SendInvoice;

class Invoice extends Model
{
  use \App\Traits\TraitUniqueHash {
    \App\Traits\TraitUniqueHash::boot as traitUniqueHashBoot;
  }

  use \App\Traits\TraitTimestampsFormatting;
  use \Illuminate\Foundation\Bus\DispatchesJobs;
  use HasFactory;

  protected $attributes = [
    /**
     * if set to active for the first time, a next schedule will be
     * created.
     *
     * if set to inactive, the upcoming schedule will be cancelled
     */
    'status' => 'inactive',

    /**
     * will find the closest day on or before the scheduled date. for
     * example, the frequency is bi-monthly, meaning, the target
     * schedule days are on 15th and [28, 30, 31] depending on the
     * month. let's say the 15th day lands on a friday but the
     * schedule_day is wednesday, we will count 2 days before the
     * 15th, hence wednesday the 13th.
     */
    'schedule_day' => 'wednesday',

    /**
     * GMT0 time when the invoice will be sent
     */
    'schedule_time' => '13:00',

    /**
     * monthly = every 28th, 30th or 31st of the month, depending on
     * which month
     *
     * bi-monthly = one on the 15th, another on the 28th, 30th or 31st
     * of the month
     */
    'frequency' => 'bi-monthly',

    /**
     * will be padded with 5 zeroes in the invoice. will also
     * increment based on the name
     */
    'invoice_no' => 1,
  ];

  public static function boot(){
    parent::boot();
    self::traitUniqueHashBoot();

    $creationCallback = function ($model){
      try {
        $createdBy = \App\Models\User::where('email', 'webmaster@newmediastaff.com')
          ->firstOrFail();
        $model->created_by = $createdBy->id;
      } catch (\Throwable $th) {
        throw new \Error("WebmasterNotFoundException");
      }
    };

    $saveCallback = function ($model){
      if($model->status != $model->getOriginal('status')){
        // Log::channel("mydebug")->info($model->hash." save callback {$model->status} != {$model->getOriginal('status')}");
        // Log::channel("mydebug")->info($model->hash." changed to ".$model->status." from ".$model->getOriginal('status'));
        if($model->status == "active")
          $model->setToActive();
        else if($model->status == "inactive")
          $model->setToInactive();
      }
    };

    static::creating($creationCallback);
    static::updated($saveCallback);
    static::created($saveCallback);
  }

  public function setToInactive(){
    $this->refresh();
    if($this->job)
      $this->job->delete();
    // Log::channel("mydebug")->info($this->hash." deactivated");
  }

  public function setToActive(){
    $this->refresh();
    $this->scheduleNextAutoInvoice();
    // Log::channel("mydebug")->info($this->hash." activated");
  }

  public function job(){
    return $this->hasOne(Job::class, "id", "current_job");
  }

  public function getNextScheduleDates(Carbon $now, $frequency, $scheduleTime){
    $dates = [];

    $dates[0] = $now;

    // set the hour and minute
    $hourmin = explode(":", $scheduleTime);

    // get closest to mid-month date with preferred day and time
    $midmonth = (clone $now)->set("day", 15);
    if(strcasecmp($midmonth->format("l"), $this->schedule_day) !== 0){
      $midmonth = $midmonth->previous($this->schedule_day);
    }
    $midmonth->startOfDay();
    $midmonth->hour($hourmin[0]);
    $midmonth->minute($hourmin[1]);
    $dates[15] = $midmonth;

    // get the closest to end-month date with preferred day and time
    $endOfMonth = (clone $now)->endOfMonth();
    if(strcasecmp($endOfMonth->format("l"), $this->schedule_day) !== 0){
      $endOfMonth = $endOfMonth->previous($this->schedule_day);
    }
    $endOfMonth->startOfDay();
    $endOfMonth->hour($hourmin[0]);
    $endOfMonth->minute($hourmin[1]);

    $dates[30] = $endOfMonth;

    // return $dates; // for debugging only

    if($dates[15]->gte($now) && strcasecmp($frequency, "bi-monthly") === 0)
      return $dates[15];
    else if($dates[30]->gte($now))
      return $dates[30];
    else
      // if 15th and 30th are earlier days than now then we need to
      // move 1 week forward.
      return $this->getNextScheduleDates((clone $now)->addWeek(), $frequency, $scheduleTime);

    return $dates;
  }

  /**
   * if today is the 15th or the last day of the month then the
   * schedule may run today. base it as well to the preferred time
   *
   * for example if preferred time is 5PM and the invoice was
   * updated at around 9am today at june 15. the invoice is following
   * the bi-monthly frequency then the next schedule is june 15 5pm
   * which is later today.
   *
   * but if the preferred time is 5PM and the invoice was updated at
   * around 5:10PM, the next schedule is last preferred day of june
   * at 5PM
   */
  public function getNextSchedule($dateNow = null, $setTimezones = true)
  {
    $now = Carbon::parse($dateNow);

    if($setTimezones){
      // convert to preferred timezone
      $tz = new CarbonTimeZone($this->timezone);
      $now->setTimezone($tz);
    }

    $date = $this->getNextScheduleDates($now, $this->frequency, $this->schedule_time);

    // convert to default app timezone to save to database
    // this is usually GMT0
    if($setTimezones)
      $date->setTimezone(config("app.timezone"));

    // return $date->format("r e \T\E\S\T"); // for debug
    return $date;
  }

  /**
   * triggers on
   * - invoice updated from inactive to active
   * - last autoinvice has been sent
   */
  public function scheduleNextAutoInvoice(){
    // do not update updated_at
    $this->timestamps = false;
    /**
     * if there is a previously scheduled job for this invoice, remove
     * it
     */
    if($this->job){
      $this->job->delete();
    }

    $this->invoice_no += 1;

    // we advance now by 5 minutes so that we won't create a new
    // schedule that collides with the time now.
    $now = Carbon::parse("+5 minutes");
    $scheduleDate = $this->getNextSchedule($now);
    // Log::channel("mydebug")->info("scheduling " . $this->hash . " on ". $scheduleDate . " now is ". $now);

    $jobID = $this->dispatch((new SendInvoice($this))->delay($scheduleDate));

    $this->current_job = $jobID;
    $this->save();

    return $jobID;
  }

  /**
   * triggers
   * - invoice updated from active to inactive
   */
  public function cancelAutoInvoice(){
    // do not update updated_at
    $this->timestamps = false;
    // if($this->current_job){
    //   DB::table('jobs')
    //     ->where('id', $this->current_job)
    //     ->delete();

    //   $this->current_job = null;
    //   $this->save();
    // }
    // shortcut of above
    $this->job->delete();
  }

  /**
   * usage: field will appear like contact_details.name
   */
  public function getDetails($field, $valueIfNull = "NA"){
    $fieldSections = explode(".", $field);

    if(count($fieldSections) < 2)
      return $valueIfNull;

    if(!isset($this[$fieldSections[0]]))
      return $valueIfNull;

    $json = json_decode($this[$fieldSections[0]], true);

    if(!isset($json[$fieldSections[1]]))
      return $valueIfNull;

    return $json[$fieldSections[1]];
  }

  public function getRange($date, $iterations = 0){
    $now = Carbon::parse($date);
    // $now = Carbon::now();
    $now->startOfDay();

    $start = clone $now;
    $start->day = 1;
    $end = clone $now;
    $end->endOfMonth();

    if($now->between(
      // (clone $start)->addSeconds(1),
      // (clone $end)->addSeconds(1)
      $start, $end
    ) && $iterations < 1){
      // \Barryvdh\Debugbar\Facades\Debugbar::info(
      //   [$now->toDayDateTimeString(), "is between", $start->toDayDateTimeString(), $end->toDayDateTimeString()]);
      if($this->frequency == "bi-monthly"){
        $now->subDays(15);
      }else if($this->frequency == "monthly"){
        // $now->subDays($now->daysInMonth); // dangerous
        $now->subMonth(1);
      }
      return $this->getRange($now, $iterations + 1);
    }

    // declare range
    $range;

    if($this->frequency == "bi-monthly"){
      if($now->day > 15){
        $start->day = 15;
        $end->day = $end->daysInMonth;
      }else{
        $end->day = 15;
      }
    }

    $range = $start->format("F j") . "-" . $end->format("j, Y");

    return $range;
  }

  public function getInvoiceLines($date){
    if(trim($this->invoice_lines) == null)
      return [];

    $json = json_decode($this->invoice_lines, true);

    // apply string replacement strategy
    foreach ($json as $jKey => $jValue) {
      if(isset($jValue["description"])){
        $json[$jKey]["description"] = strtr($jValue["description"],
          [
            ":range" => $this->getRange($date)
          ]);
      }
    }

    // \Barryvdh\Debugbar\Facades\Debugbar::info($json);
    return $json;
  }

  public function getInvoiceNo(){
    return str_pad($this->invoice_no, 5, "0", STR_PAD_LEFT);
  }

  public function formatAmount($amount){
    return $this->currency ." ". number_format($amount,2);
  }

  public function getTotalAmount($invoiceLines){
    $total = 0;
    foreach ($invoiceLines as $key => $value) {
      $total += floatval($value["amount"]);
    }

    return $total;
  }

  public function generatePDF($date = null){
    $now = Carbon::parse($date);
    $tz = new CarbonTimeZone($this->timezone);
    $now->setTimezone($tz);
    $range = $this->getRange($now);
    $filename = \Illuminate\Support\Str::slug(
      $this->hash."-".$this->getInvoiceNo()."-".$range)
      .".pdf";

    $invoiceLines = $this->getInvoiceLines($now);

    $view = view('invoice-pdf',[
      "invoice" => $this,
      "date" => $now->format(config('app.generated_invoice_date_format')),
      "invoiceLines" => $this->getInvoiceLines($now),
      "totalAmount" => $this->getTotalAmount($invoiceLines),
      "range" => $range,
    ]);

    $thePath = config('app.generated_invoice_path')."/".$filename;
    PDF::loadHTML($view)->save($thePath);

    return [
      "file" => $thePath,
      "view" => $view,
    ];
  }
}
