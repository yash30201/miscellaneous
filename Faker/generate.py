#!/usr/bin/env python

from faker import Faker
import json
import sys
import base64

#
fake = Faker()

# sizes = [200]
sizes = [100, 150, 200, 250, 300, 350, 400, 500, 700, 1000, 1500, 2000, 3000, 5000, 8000, 10000, 15000, 20000, 30000, 40000, 50000];

result = []
for size in sizes:
    x = ""
    tempResult = []
    while len(x) < size:
        tempResult.append(
            {
                "tweet": fake.text(max_nb_chars=50),
                "handle": fake.first_name(),
                "email": fake.email(),
            }
        )
        x = base64.b64encode(json.dumps(tempResult).encode("utf-8"))
    result.append(tempResult)

# Creates a json file json array containing the size and text of the data
finalResult = json.dumps(result)
print("Size of result is {}".format(len(finalResult.encode("utf-8"))))
file = open("data/dataY.json", "w+")
file.write(finalResult)
file.close()
