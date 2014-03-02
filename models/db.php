<?php
    function db( $sql, $bind = [] ) {
        foreach( $bind as $key => $value ) {
            if ( is_string( $value ) ) {
                $value = mysql_real_escape_string( $value );
                $value = '"' . $value . '"';
            }
            else if ( is_array( $value ) ) {
                foreach ( $value as $i => $subvalue ) {
                    $value[ $i ] = addslashes( $subvalue );
                }
                $value = "( '" . implode( "', '", $value ) . "' )";
            }
            else if ( is_null( $value ) ) {
                $value = '""';
            }
            $bind[ ':' . $key ] = $value;
            unset( $bind[ $key ] );
        }
        $finalsql = strtr( $sql, $bind );
        $res = mysql_query( $finalsql );
        if ( $res === false ) {
            throw new DBException( mysql_error() );
        }
        return $res;
    }

    function dbInsert( $table, $row ) {
        return dbInsertMulti( $table, [ $row ] );
    }

    function dbInsertMulti( $table, $rows ) {
        if ( empty( $rows ) ) {
            // nothing to do here
            return;
        }
        if ( empty( $rows ) ) {
            $setString = ' () VALUES ()';
        }
        else {
            $keys = '(';
            $firstRow = $rows[ 0 ];
            if ( count( $firstRow ) ) {
                $fields = [];
                foreach ( $firstRow as $field => $value ) {
                    $keys .= "$field,";
                }
                $keys = substr( $keys, 0, strlen( $keys ) - 1 );
            }
            $keys .= ')';
            $values = 'VALUES ';
            $i = 0;
            $bind = [];
            foreach ( $rows as $row ) {
                $valuePlaceHolders = '(';
                ++$i;
                if ( count( $row ) ) {
                    foreach ( $row as $key => $value ) {
                        $valuePlaceHolders .= ":$key$i,";
                        $bind[ "$key$i" ] = $value;
                    }
                    $valuePlaceHolders = substr( $valuePlaceHolders, 0, strlen( $valuePlaceHolders ) - 1 );
                }
                $valuePlaceHolders .= ')';
                $values .= "$valuePlaceHolders,";
            }
            $values = substr( $values, 0, strlen( $values ) - 1 );
            $setString = $keys . $values;
        }
        $res = db(
            'INSERT INTO '
            . $table
            . $setString,
            $bind
        );
        return mysql_insert_id();
    }

    function dbDelete( $table, $where ) {
        $fields = [];
        foreach ( $where as $field => $value ) {
            $fields[] = "$field = :$field";
        }
        db(
            'DELETE FROM '
            . $table
            . ' WHERE '
            . implode( " AND ", $fields ),
            $where
        );
        return mysql_affected_rows();
    }

    function dbSelect( $table, $select = [ "*" ], $where = [] ) {
        $fields = [];
        foreach ( $where as $field => $value ) {
            $fields[] = "$field = :$field";
        }
        $sql =  'SELECT ' . implode( ",", $select ) . ' FROM ' . $table;
        if ( !empty( $where ) ) {
            $sql = $sql . ' WHERE ' . implode( " AND ", $fields );
        }
        return dbArray(
            $sql,
            $where
        );
    }

    function dbSelectOne( $table, $select = [ "*" ], $where = [] ) {
        $array = dbSelect( $table, $select, $where );
        if ( count( $array ) > 1 ) {
            throw new DBException( 'select one with multiple results' );
        }
        else if ( count( $array ) < 1 ) {
            throw new DBException( 'select one with no results' );
        }
        return $array[ 0 ];
    }

    function dbUpdate( $table, $set, $where ) {
        $wfields = [];
        $wreplace = [];
        foreach ( $where as $field => $value ) {
            $wfields[] = "$field = :where_$field";
            $wreplace[ 'where_' . $field ] = $value;
        }
        $sfields = [];
        $sreplace = [];
        foreach ( $set as $field => $value ) {
            $sfields[] = "$field = :set_$field";
            $sreplace[ 'set_' . $field ] = $value;
        }
        db(
            'UPDATE '
            . $table
            . ' SET '
            . implode( ",", $sfields )
            . ' WHERE '
            . implode( " AND ", $wfields ),
            array_merge( $wreplace, $sreplace )
        );
        return mysql_affected_rows();
    }

    function dbArray( $sql, $bind = [], $id_column = false ) {
        $res = db( $sql, $bind );
        $rows = [];
        if ( $id_column !== false ) {
            while ( $row = mysql_fetch_array( $res ) ) {
                $rows[ $row[ $id_column ] ] = $row;
            }
        }
        else {
            while ( $row = mysql_fetch_array( $res ) ) {
                $rows[] = $row;
            }
        }
        return $rows;
    }

    function dbListTables() {
        return array_map( 'array_shift', dbArray( 'SHOW TABLES' ) );
    }

    class DBException extends Exception {
        public function __construct( $error ) {
            parent::__construct( 'Database error: ' . $error );
        }
    }
?>
