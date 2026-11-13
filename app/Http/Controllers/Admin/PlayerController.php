<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Support\Activity;
use App\Support\SquadAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PlayerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $teams = SquadAccess::teamsFor($user);
        $query = Player::query()->with('teamRelation');

        if ($user->isCoach()) {
            $this->scopeToCoachTeam($query, $user);
        } elseif ($request->filled('team')) {
            $query->where('team', $request->get('team'));
        }

        if ($request->filled('status')) {
            $query->where('is_published', $request->get('status') === 'published');
        }

        if ($search = trim((string) $request->get('q'))) {
            $query->where(fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('role', 'like', "%{$search}%"));
        }

        $players = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.players.index', compact('players', 'teams'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $teams = SquadAccess::teamsFor($user);
        $team = $user->isCoach() ? $teams->first() : null;

        $player = new Player([
            'is_published' => true,
            'team' => $team?->category ?? 'men',
            'team_id' => $team?->id,
        ]);

        return view('admin.players.create', compact('player', 'teams'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, requireImage: true);
        $data = $this->forceCoachTeam($request, $data);
        $data['slug'] = unique_slug($data['name'], Player::class);
        $data['image'] = upload_media($request->file('image'), 'players');
        $data = $this->normalise($data);

        $player = Player::create($data);
        Activity::log('created', $player, 'Added player: '.$player->name);

        return redirect()->route('admin.players.index')->with('success', 'Player added successfully.');
    }

    public function show(Player $player)
    {
        return redirect()->route('admin.players.edit', $player);
    }

    public function edit(Request $request, Player $player)
    {
        $this->guardCoach($request, $player);

        $teams = SquadAccess::teamsFor($request->user());

        return view('admin.players.edit', compact('player', 'teams'));
    }

    public function update(Request $request, Player $player)
    {
        $this->guardCoach($request, $player);

        $data = $this->validated($request);
        $data = $this->forceCoachTeam($request, $data);
        $data['slug'] = unique_slug($data['name'], Player::class, $player->id);

        if ($request->hasFile('image')) {
            $data['image'] = upload_media($request->file('image'), 'players', $player->image);
        } else {
            unset($data['image']);
        }

        if ($request->boolean('remove_image')) {
            delete_media($player->image);
            $data['image'] = null;
        }

        $player->update($this->normalise($data));
        Activity::log('updated', $player, 'Updated player: '.$player->name);

        return redirect()->route('admin.players.index')->with('success', 'Player updated successfully.');
    }

    public function destroy(Player $player)
    {
        delete_media($player->image);
        $name = $player->name;
        $player->delete();
        Activity::log('deleted', null, 'Removed player: '.$name);

        return back()->with('success', 'Player removed.');
    }

    private function validated(Request $request, bool $requireImage = false): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'team_id' => ['nullable', 'exists:teams,id'],
            'team' => ['required', 'string', 'max:40'],
            'image' => [$requireImage ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:8192'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'age' => ['nullable', 'integer', 'min:10', 'max:80'],
            'role' => ['nullable', 'string', 'max:80'],
            'position_category' => ['nullable', 'string', 'max:40'],
            'special_role' => ['nullable', 'string', 'max:40'],
            'weight' => ['nullable', 'string', 'max:20'],
            'height' => ['nullable', 'string', 'max:20'],
            'games' => ['nullable', 'integer', 'min:0'],
            'points' => ['nullable', 'integer', 'min:0'],
            'tries' => ['nullable', 'integer', 'min:0'],
            'place_of_birth' => ['nullable', 'string', 'max:120'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'honours' => ['nullable', 'string'],
            'joined' => ['nullable', 'string', 'max:20'],
            'previous_clubs' => ['nullable', 'string'],
            'sponsor' => ['nullable', 'string', 'max:120'],
            'sponsor_desc' => ['nullable', 'string'],
        ], [], [
            'image' => 'profile picture',
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }

    private function normalise(array $data): array
    {
        foreach (['games', 'points', 'tries'] as $field) {
            $data[$field] = (int) ($data[$field] ?? 0);
        }

        if (! empty($data['date_of_birth'])) {
            $data['age'] = Carbon::parse($data['date_of_birth'])->age;
        }

        if (! empty($data['team_id'])) {
            $team = Team::find($data['team_id']);
            if ($team) {
                $data['team'] = $team->category;
            }
        }

        return $data;
    }

    /**
     * Limit a listing to the squad the coach is responsible for.
     */
    private function scopeToCoachTeam(Builder $query, User $user): void
    {
        $team = SquadAccess::team($user, null);

        if (! $team) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(fn (Builder $q) => $q->where('team_id', $team->id)->orWhere('team', $team->category));
    }

    /**
     * A coach may only open players that belong to their own squad.
     */
    private function guardCoach(Request $request, Player $player): void
    {
        $user = $request->user();

        if (! $user->isCoach()) {
            return;
        }

        $team = SquadAccess::team($user, null);
        $owns = $team
            && ((int) $player->team_id === (int) $team->id || $player->team === $team->category);

        abort_unless($owns, 403, 'You can only manage players in your own squad.');
    }

    /**
     * A coach always adds or updates players inside their own squad.
     */
    private function forceCoachTeam(Request $request, array $data): array
    {
        $user = $request->user();

        if (! $user->isCoach()) {
            return $data;
        }

        $team = SquadAccess::team($user, null);
        abort_unless($team, 403, 'Your account is not linked to a squad yet.');

        $data['team_id'] = $team->id;
        $data['team'] = $team->category;

        return $data;
    }
}
