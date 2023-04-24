tin="Hello"

if [ -z "$1" ]
  then
    tin='Hi'
  else
    tin=$1
fi

echo $tin
