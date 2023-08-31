<?php

namespace App\Http\Controllers;

use App\Models\Chirp;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;


class ChirpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    //public function index()
    public function index(): View
    {
        //
        return view('chirps.index', [
            'chirps' => Chirp::with('user')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    
    }

    public function assignUser(int $id)
    {
        $chirp = Chirp::find($id);
        // Récupère la liste des autres utilisateurs enregistrés
        $otherUsers = User::where('id', '!=', auth()->user()->id)->get();

        // Affiche la vue pour la sélection de l'utilisateur copropriétaire
        return view('chirps.coproprio', compact('chirp', 'otherUsers'));

    }

    /**
     * Store a newly created resource in storage.
     */
    //public function store(Request $request)
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'message' => 'required|string|max:255',
        ]);
    
        // Créer le chirp
        $chirp = Chirp::create([
            'message' => $request->input('message'),
        ]);
    
        // Associer l'utilisateur connecté au chirp
        auth()->user()->chirps()->attach($chirp);
    
        // Rediriger ou effectuer d'autres traitements après avoir créé le chirp
        return redirect()->route('chirps.index')->with('success', 'Chirp créé avec succès.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Chirp $chirp)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    //public function edit(Chirp $chirp)
    public function edit(Chirp $chirp): View
    {
        //
        // Vérification de l'autorisation d'accéder à l'édition
        $this->authorize('update', $chirp);

        return view('chirps.edit', [
            'chirp' => $chirp,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    //public function update(Request $request, Chirp $chirp)
    public function update(Request $request, Chirp $chirp): RedirectResponse
    {
        //
        $this->authorize('update', $chirp);

        $validated = $request->validate([
            'message' => 'required|string|max:255',
        ]);

        $chirp->update($validated);

        return redirect(route('chirps.index'));
    }

    /**
     * Remove the specified resource from storage.
     */
    //public function destroy(Chirp $chirp)
    public function destroy(Chirp $chirp): RedirectResponse
    {
        //
        $this->authorize('delete', $chirp);

        $chirp->delete();

        return redirect(route('chirps.index'));
    }

     public function adder(User $user)
    {
            // utilisateur n'est pas déjà associé au chirp?
            if (!$this->users->contains($user)) {
                $this->users()->attach($user);
            }
     }

     public function addCoauthor(Request $request, Chirp $chirp)
     {
         // valider les données du formulaire
         $request->validate([
             'coproprietaire_id' => 'required|exists:users,id',
         ]);
     
         // recup l'ID de l'utilisateur sélectionné
         $coproprietaireId = $request->input('coproprietaire_id');
     
         // recuperer user à partir de l'ID
         $coproprietaire = User::find($coproprietaireId);
     
         if (!$coproprietaire) {
             // utilisateur n'a pas été trouvé
             return redirect()->route('chirps.index')->with('error', 'Utilisateur non trouvé.');
         }
     
         // add l'utilisateur comme coauteur au chirp
         $chirp->users()->attach($coproprietaire);
     
         // au cas succes , redirection
         return redirect()->route('chirps.index')->with('success', 'Coauteur ajouté avec succès.');
     }
     

}
