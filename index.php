<?php
/**
 * Plugin Name: League Show Profiler Plugin
 * Plugin URI: http://www.leagueshow.com
 * Description: Shows profiles of League of Legends accounts and details.
 * Version: 0.1
 * Author: Alex Lanzoni (TheLanzolini)
 * Author URI: http://www.leagueshow.com
 * License: LSP
 */
/* Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : PLUGIN AUTHOR EMAIL)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

add_action( 'admin_menu', 'lsp_menu' );

add_action( 'wp_enqueue_scripts', 'register_plugin_styles' );
function register_plugin_styles() {
    wp_register_style( 'main', plugins_url( 'lsp_profiler/lsp_main.css' ) );
    wp_enqueue_style( 'main' );
}

function leagueshow_func( $atts ){
	extract( shortcode_atts( array(
		'summoner' => null
	), $atts ) );
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_URL, "https://prod.api.pvp.net/api/lol/na/v1.1/summoner/by-name/".$summoner."?api_key=c0255ae8-0898-4904-be03-9accec1817ca");  
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $s_summoner = curl_exec($ch);  
        $s_summoner = json_decode($s_summoner);
        curl_setopt($ch, CURLOPT_URL, "https://prod.api.pvp.net/api/lol/na/v1.1/game/by-summoner/".$s_summoner->id."/recent?api_key=c0255ae8-0898-4904-be03-9accec1817ca");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $recent = curl_exec($ch);
        $recent = json_decode($recent);
        curl_setopt($ch, CURLOPT_URL, "https://prod.api.pvp.net/api/na/v2.1/league/by-summoner/".$s_summoner->id."?api_key=c0255ae8-0898-4904-be03-9accec1817ca");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $league = curl_exec($ch);
        $league = json_decode($league);
        curl_setopt($ch, CURLOPT_URL, "https://prod.api.pvp.net/api/lol/na/v1.1/champion?api_key=c0255ae8-0898-4904-be03-9accec1817ca");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $champions = curl_exec($ch);
        $champions = json_decode($champions);
        curl_setopt($ch, CURLOPT_URL, "https://prod.api.pvp.net/api/lol/na/v1.1/stats/by-summoner/".$s_summoner->id."/summary?season=SEASON3&api_key=c0255ae8-0898-4904-be03-9accec1817ca");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $stats = curl_exec($ch);
        $stats = json_decode($stats);
        $s_id = $s_summoner->id;
        $tier_entries = $league->$s_id->entries;
        foreach($tier_entries as $entry){
            if($entry->playerOrTeamName == $s_summoner->name){
                $s_tier = $entry->tier;
                $s_rank = $entry->rank;
                $s_points = $entry->leaguePoints;
            }    
        }
        if(!curl_exec($ch)){
            die('Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch));
        }
        curl_close($ch);
        echo '<div class="lsp_banner">';
        echo '<div class="lsp_s_name">';
        echo '<img class="lsp_profileicon" src="'. plugins_url( 'lsp_profiler/profile-icons/'.$s_summoner->profileIconId.'.png').'">';        
        echo '<span class="lsp_name">'.$s_summoner->name.'</span>';
        echo '<span class="lsp_league">'.' '.$s_tier.' '.$s_rank.' '.$s_points.'pts'.'</span>';
        foreach($stats->playerStatSummaries as $s){
            if($s->playerStatSummaryType == 'Unranked'){
                echo '<span class="lsp_league">'.$s->wins.' Normal Wins</span>';
            }
            if($s->playerStatSummaryType == 'RankedSolo5x5'){
                echo '<span class="lsp_league">'.$s->wins.'Ranked Wins</span>';
            }
        }
        echo '</div>';
        echo '<span class="recent_label">RECENT MATCHES</span>';
        echo '<div class="lsp_recent">';
        foreach($recent->games as $game){
            echo '<div class="lsp_recent_thumb">';
            foreach($champions->champions as $champion){
                if($game->championId == $champion->id){
                    if($game->subType == 'ARAM_UNRANKED_5x5'){
                        echo '<span class="subtype">ARAM</span>';
                    }else if($game->subType == 'RANKED_SOLO_5x5'){
                        echo '<span class="subtype">RANKED</span>';
                    }else if($game->subType == 'NORMAL'){
                        echo '<span class="subtype">NORMAL</span>';
                    }else if($game->subType == 'ODIN_UNRANKED'){
                        echo '<span class="subtype">DOMINION</span>';
                    }else if($game->subType == 'RANKED_TEAM_5x5'){
                        echo '<span class="subtype">RANKED TEAM</span>';
                    }else{
                        echo 'OTHER';
                    }
                    echo '<br />';    
                    echo '<img class="lsp_championicon" src="'. plugins_url( 'lsp_profiler/champion-squares/'.$champion->name.'_Square_0'.'.png').'">';
                }
            }
            echo'</div>';
        }
        echo '</div>';
        echo '</div>';  
}
add_shortcode( 'leagueshow', 'leagueshow_func');

function lsp_menu() {
	add_options_page( 'LS Profiler Options', 'LS Profiler Options', 'manage_options', 'league-show', 'lsp_options' );
}

function lsp_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<div class="lsp_wrap">'; 
	echo'<h3>League Show Profiler Options</h3>';
	echo'';
	echo '</div>'; 
} 


?>