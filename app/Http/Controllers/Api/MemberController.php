<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $search_term = $request->input('q');
        
        if($search_term) {
            $members = Member::where('name', 'LIKE', '%'.$search_term.'%')
                ->orWhere('member_numb', 'LIKE', '%'.$search_term.'%')
                ->orWhere('id_card', 'LIKE', '%'.$search_term.'%')
                ->paginate(10);
            $members->map(function($member) {
                $member->text = $member->member_numb . ' - ' . $member->name;
                return $member;
            });
        } else {
            $members = Member::paginate(10);
            $members->map(function($member) {
                $member->text = $member->member_numb . ' - ' . $member->name;
                return $member;
            });
        }
        return $members;
    }

    public function notActivated(Request $request){
        $search_term = $request->input('q');
        
        if($search_term) {
            $members = Member::where('name', 'LIKE', '%'.$search_term.'%')
                ->orWhere('member_numb', 'LIKE', '%'.$search_term.'%')
                ->orWhere('id_card', 'LIKE', '%'.$search_term.'%')
                ->paginate(10);
            $members->map(function($member) {
                $text = $member->member_numb . ' - ' . $member->name;
                if ($member->expired_at == null || $member->expired_at < now()) {
                    $text .= " (Expired)";
                } else {
                    $text .= " (Active)";
                }
                $member->text = $text;
                return $member;
            });
        } else {
            $members = Member::paginate(10);
            $members->map(function($member) {
                $text = $member->member_numb . ' - ' . $member->name;
                if ($member->expired_at == null || $member->expired_at < now()) {
                    $text .= " (Expired)";
                } else {
                    $text .= " (Active)";
                }
                $member->text = $text;
                return $member;
            });
        }
        return $members;
    }

    public function onlyActive(Request$request){
        $search_term = $request->input('q');
        if($search_term) {
            $members = Member::active()->where(function ($query) use ($search_term) {
                $query->where('name', 'LIKE', '%'.$search_term.'%')
                    ->orWhere('member_numb', 'LIKE', '%'.$search_term.'%')
                    ->orWhere('id_card', 'LIKE', '%'.$search_term.'%');
                })
                ->paginate(10);
                $members->map(function($member) {
                    $text = $member->member_numb . ' - ' . $member->name;
                    $member->text = $text;
                    return $member;
                });
        } else {
            $members = Member::active()->paginate(10);
            $members->map(function($member) {
                $text = $member->member_numb . ' - ' . $member->name;
                $member->text = $text;
                return $member;
            });
        }
        return $members;
    }
}
