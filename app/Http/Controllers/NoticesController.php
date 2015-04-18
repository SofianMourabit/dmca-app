<?php namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Notice;
use App\Provider;
use Auth;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Requests\PrepareNoticeRequest;
use Illuminate\Support\Facades\Mail;

class NoticesController extends Controller {

    /**
     *Create a new notices controller instance
     */
    public function __construct()
    {
        $this->middleware('auth');

        parent:: __construct();
    }


    /**
     * Show all notices
     *
     * @return string
     */
    public function index()
    {
        $notices = $this->user->notices;

        return view('notices.index', compact('notices'));
    }

    /**
     * Show a page to create all notices
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // get list of providers
        // load a view

        $providers =  Provider::lists('name', 'id');

        return view('notices.create', compact('providers'));

    }

    /**
     * Ask the user to confirm the DMCA that will be delivered.
     *
     * @param PrepareNoticeRequest $request
     * @param Guard $auth
     * @return \Illuminate\View\View
     */
    public function confirm(PrepareNoticeRequest $request)
    {
        $template = $this->compileDmcaTemplate($data = $request->all());

        session()->flash('dmca', $data);

        return view('notices.confirm', compact('template'));
    }


    /**
     * Store a new DMCA notice.
     *
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $notice = $this->createNotice($request);

        Mail::queue(['text' => 'emails.dmca'], compact('notice'), function($message) use ($notice){
            $message->from($notice->getOwnerEmail())
                ->to($notice->getRecipientEmail())
                ->subject('DMCA Notice');
        });

        flash('Your DMCA notice has been delivered!');

        return redirect('notices');

    }

    public function update($noticeId, Request $request)
    {
        $isRemoved = $request->has('content_removed');

        Notice::findOrFail($noticeId)
            ->update(['content_removed' => $isRemoved]);

    }

    /**
     * Compile the DMCA template froom the form data.
     *
     * @param $data
     * @param Guard $auth
     * @return mixed
     */
    public function compileDmcaTemplate($data)
    {

        $data = $data +[
                'name' => $this->user->name,
                'email' => $this->user->email,
            ];

        return view()->file(app_path('Http/Templates/dmca.blade.php'), $data);

    }

    /**
     * Create and persist a new DMCA notice
     *
     * @param Request $request
     */
    public function createNotice(Request $request)
    {
        $notice = session()->get('dmca') + ['template' => $request->input('template')];

        $notice = $this->user->notices()->create($notice);

        return $notice;
    }

}
