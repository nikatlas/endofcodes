$( document ).ready( function() {
    $( '.creature' ).mouseover( function () {
        var ARROW_HEIGHT = 14;
        var ARROW_WIDTH = 30;
        var id = this.getAttribute( 'data-creatureid' );
        var username = this.getAttribute( 'data-username' );
        var x = this.getAttribute( 'data-x' );
        var y = this.getAttribute( 'data-y' );
        var hp = this.getAttribute( 'data-hp' );
        var maxHp = this.getAttribute( 'data-maxHp' );
        var $this = $( this );
        var offsetTop = $this.offset().top - $( '.infobubble' ).height() - 14;
        var $infobubble = $( '.infobubble' );
        var positioning;

        $infobubble.show();
        $( '.player' ).text( username );
        $( '.creatureid' ).text( 'Creature ' + id );
        $( '.location' ).text( x + ', ' + y );
        $( '.numeric' ).text( hp + ' / ' + maxHp );
        $( '.damage' ).css( 'width', Math.floor( 100 * ( maxHp - hp ) / maxHp ) + '%' );
        if ( offsetTop < 0 ) {
            positioning = $this.height() + ARROW_HEIGHT;
            $infobubble.addClass( 'reversed' );
        }
        else {
            positioning = -$infobubble.height() - ARROW_HEIGHT;
        }
        $infobubble.css( 'top', $this.position().top + positioning );
        $infobubble.css( 'left', $this.position().left - $infobubble.width() + ARROW_WIDTH );
    } );
    $( '.creature' ).mouseout( function() {
        var $infobubble = $( '.infobubble' );
        $infobubble.removeClass( 'reversed' );
        $infobubble.hide();
    } );
    function findGameAndRoundId( href ) {
        var hrefArray = href.substr( href.indexOf( "?" ) + 1 ).split( "&" );
        for ( var i = 0; i < hrefArray.length; ++i ) {
            attribute = hrefArray[ i ].split( "=" );
            if ( attribute[ 0 ] == 'roundid' ) {
                roundid = parseInt( attribute[ 1 ] );
            }
            else if ( attribute[ 0 ] == 'gameid' ) {
                gameid = parseInt( attribute[ 1 ] );
            }
        }
        return {
            gameid: gameid,
            roundid: roundid
        }
    }
    function getMap( href, roundValue ) {
        $.getJSON( href, function( creatures ) {
            var maxHp = $( '.creature' ).attr( 'data-maxHp' );
            var gameInfo = findGameAndRoundId( href );
            var nextHref = $( '.next a' ).attr( 'href' );
            var previousHref = $( '.previous a' ).attr( 'href' );
            var prefix = "game/view?gameid=" + gameInfo.gameid + "&roundid=";

            $( '.next a' ).attr( 'href', prefix + ( findGameAndRoundId( nextHref ).roundid + roundValue ) );
            $( '.previous a' ).attr( 'href', prefix + ( findGameAndRoundId( previousHref ).roundid + roundValue ) );
            $( '.round' ).text( 'Round ' + findGameAndRoundId( href ).roundid );

            $( '.creature' ).remove();
            for ( var i = 0; i < creatures.length; ++i ) {
                var creature = creatures[ i ];
                if ( creature.hp > 0 ) {
                    var $user = $( '.playerList li[data-id=' + creature.userid + ']' );
                    var username = $user.text();
                    var color = $user.find( 'span.bubble' ).attr( 'data-color' );
                    creatureInfo = {
                        creatureid: creature.id,
                        username: username,
                        x: creature.x,
                        y: creature.y,
                        hp: creature.hp,
                        maxHp: maxHp
                    };
                    $creature = $( '<div class="' + color + ' creature"></div>' );
                    for ( var attribute in creatureInfo ) {
                        var value = creatureInfo[ attribute ];
                        $creature.attr( 'data-' + attribute, value );
                    }
                    $creature.css( {
                        left: creature.x * 20 + 'px',
                        top: creature.y * 20 + 'px'
                    } );
                }
                $( '.gameboard' ).prepend( $creature );
            }
        } );
    }
    $( '.next a' ).click( function() {
        getMap( this.href, 1 );
        return false;
    } );
    $( '.previous a' ).click( function() {
        getMap( this.href, -1 );
        return false;
    } );
} );
