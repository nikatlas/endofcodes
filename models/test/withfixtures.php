<?php
    include_once 'models/game.php';
    include_once 'models/user.php';
    include_once 'models/round.php';
    include_once 'models/creature.php';
    include_once 'models/country.php';
    class UnitTestWithFixtures extends UnitTest {
        protected function buildUser( $username ) {
            global $config;

            $user = new User();
            $user->username = $username;
            $user->password = 'secret1234';
            $user->email = "$username@gmail.com";
            $user->boturl = $config[ 'base' ] . 'bots/php';
            $user->save();

            return $user;
        }
        protected function buildCountry( $name, $shortname ) {
            $country = new Country();
            $country->name = $name;
            $country->shortname = $shortname;
            $country->save();

            return $country;
        }
        protected function buildCreature( $id, $x, $y, $user, $game = false ) {
            $creature = new Creature();
            $creature->locationx = $x;
            $creature->locationy = $y;
            $creature->hp = 10;
            $creature->user = $user;
            $creature->id = $id;
            if ( $game === false ) {
                $creature->game = new Game();
                $creature->game->id = 1;
            }
            else {
                $creature->game = $game;
            }
            $creature->intent = new Intent( ACTION_NONE, DIRECTION_NONE );
            $creature->save();

            return $creature;
        }
        protected function buildRound() {
            $round = new Round();
            $round->id = 1;
            $creature1 = $this->buildCreature( 1, 1, 2, $this->buildUser( 'vitsalis' ) );
            $creature2 = $this->buildCreature( 2, 3, 4, $this->buildUser( 'pkakelas' ) );
            $round->creatures = [ $creature1, $creature2 ];
            return $round;
        }
        protected function buildGame() {
            $game = new Game();
            for ( $i = 1; $i <= 4; ++$i ) {
                $game->users[] = $this->buildUser( $i );
            }
            $game->save();
            return $game;
        }
    }
?>
