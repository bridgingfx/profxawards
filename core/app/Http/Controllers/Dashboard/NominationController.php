<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Nomination;
use App\Models\WebmasterSection;
use Auth;
use Helper;
use Illuminate\Http\Request;
use Redirect;

class NominationController extends Controller
{
    public function index()
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->newsletter_status) {
            return Redirect::to(route('NoPermission'));
        }

        // General for all pages
        $GeneralWebmasterSections = WebmasterSection::where('status', '=', '1')->orderby('row_no', 'asc')->get();
        // General END

        $Nominations = Nomination::orderby('id', 'desc')->paginate(config('smartend.backend_pagination'));

        return view("dashboard.nominations.list", compact("Nominations", "GeneralWebmasterSections"));
    }

    public function destroy($id)
    {
        // Check Permissions
        if (!@Auth::user()->permissionsGroup->delete_status) {
            return Redirect::to(route('NoPermission'));
        }

        $Nomination = Nomination::find($id);
        if (!empty($Nomination)) {
            $Nomination->delete();
        }

        return redirect()->route('nominations.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'         => 'nullable|email',
            'confirm_email' => 'nullable|email|same:email',
        ]);

        try {
            Nomination::create([
                'company'       => strip_tags($request->company),
                'contact'       => strip_tags($request->contact),
                'jobtitle'      => strip_tags($request->jobtitle),
                'email'         => strip_tags($request->email),
                'confirm_email' => strip_tags($request->confirm_email),
                'phone'         => $request->phone,
                'country'       => strip_tags($request->country),
                'description'   => $request->description,
                'statement'     => $request->statement,
                'category'      => $request->category,
                'subcategory'   => $request->subcategory,
                'consent1'      => $request->has('consent1'),
                'consent2'      => $request->has('consent2'),
            ]);

            return redirect()->back()
                ->with('success', 'Nomination submitted successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
