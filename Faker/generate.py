#!/usr/bin/env python

from faker import Faker
import json

#
fake = Faker()

sizes = [100, 150, 200, 250, 300, 350, 400, 500, 700, 1000, 1500, 2000, 2500, 3000, 3500, 4000, 5000, 7000, 8000, 9000]

result = []
for size in sizes:
    result.append({
        'size': size,
        'text': fake.text(max_nb_chars = size)
    })

# Creates a json file json array containing the size and text of the data
finalResult = json.dumps(result)
file = open("data/data.json","w+")
file.write(finalResult)
file.close()
