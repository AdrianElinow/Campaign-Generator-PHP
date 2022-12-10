import sys, json


def test_load( filename ):
    json.load( open( sys.argv[1] ) )


def main():
    
    if len(sys.argv) < 2:
        raise ValueError("Missing file arguments")

    try:
        test_load(sys.argv[1])
        print(sys.argv[1],"loaded without incident")
    except json.decoder.JSONDecodeError as de:
        print(sys.argv[1], "\n\t",de)


if __name__ == '__main__':
    main()
