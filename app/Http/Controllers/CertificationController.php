<?php

namespace App\Http\Controllers;

use App\Http\Requests\CertificationRequest;
use App\Models\Certification;
use App\Models\ProviderProfile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificationController extends Controller
{
    public function store(CertificationRequest $request)
    {
        $profile = ProviderProfile::firstOrCreate(['user_id' => $request->user()->id]);
        $filePath = $request->hasFile('file')
            ? $request->file('file')->store('certifications', 'local')
            : null;

        $profile->certifications()->create([
            'title' => $request->input('title'),
            'issuer' => $request->input('issuer'),
            'year' => $request->input('year'),
            'file_path' => $filePath,
        ]);

        return redirect(route('provider.profile.edit') . '#certifications')->with('success', 'Certification added.');
    }

    public function edit(Certification $certification)
    {
        Gate::authorize('update', $certification->providerProfile);
        return view('provider.certifications.edit', ['certification' => $certification]);
    }

    public function download(Certification $certification): StreamedResponse
    {
        Gate::authorize('update', $certification->providerProfile);

        abort_unless($certification->file_path && Storage::disk('local')->exists($certification->file_path), 404);

        return Storage::disk('local')->download(
            $certification->file_path,
            basename($certification->file_path)
        );
    }

    public function update(CertificationRequest $request, Certification $certification)
    {
        $certification->title = $request->input('title');
        $certification->issuer = $request->input('issuer');
        $certification->year = $request->input('year');

        if ($request->hasFile('file')) {
            if ($certification->file_path) {
                Storage::disk('local')->delete($certification->file_path);
            }
            $certification->file_path = $request->file('file')->store('certifications', 'local');
        }

        $certification->save();
        return redirect(route('provider.profile.edit') . '#certifications')->with('success', 'Certification updated.');
    }

    public function destroy(Certification $certification)
    {
        Gate::authorize('update', $certification->providerProfile);

        if ($certification->file_path) {
            Storage::disk('local')->delete($certification->file_path);
        }

        $certification->delete();
        return redirect(route('provider.profile.edit') . '#certifications')->with('success', 'Certification deleted.');
    }
}
