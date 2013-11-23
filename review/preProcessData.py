#!/usr/bin/python

# This file pre process data to be used in analyzeData.r
# The input into this file is from get_results.php which exports sql data

# Written by Alan Nochenson, 2/20/2013

import sys
import re
import csv
import json
import HTMLParser
from pprint import pprint
from math import ceil


def main():
    htmlParser=HTMLParser.HTMLParser();

    infh = open(sys.argv[1], 'r')
    reader = csv.reader(infh)
    data = []
    i = 0

    round_nums = {}


    newcolnames = ['unknown', 'age', 'country', 'education', 'gender', 'mturk_id', 'nfctotal', 'rpstotal', 'round_payment', 'player_flips', 'round_score', 'round_other_score', 'round_num_flips', 'round_optimal_num_flips', ' round_num_good_flips', 'round_num_too_early_flips', 'round_num_unneeded_flips', 'round_num']
    for row in reader:
        for j in range(0,len(row)):
            row[j] = htmlParser.unescape(row[j])

        if i == 0:
            for extra_col in newcolnames:
                row.append(extra_col)
            row[2] = 'round_id'
            row[11] = 'female'  # WHY??????????????????????????  DR.
        else:
            try:
                row[0] = row[0].replace('$','') #remove dollar sign from payment

                json_cols = get_json_data_from(row, newcolnames)
                for col in json_cols:
                    row.append(col)

                round_payment = get_round_payment(row)
                row.append(round_payment)

                player_flips = get_player_flips(row[6])
                row.append(player_flips)

                row.append(json.loads(row[7])[-1])
                row.append(json.loads(row[8])[-1])

                round_num_flips = len(player_flips)
                round_optimal_num_flips = ceil((2000-int(row[5]))/int(row[4]))

                row.append(round_num_flips)
                row.append(round_optimal_num_flips)

                tick=row[4]
                anchor=row[5]

                good_and_early = get_good_and_early(player_flips, tick, anchor)
                round_num_good_flips = good_and_early['good']
                round_num_too_early_flips = good_and_early['early']
                round_num_unneeded_flips = round_num_flips - round_num_good_flips - round_num_too_early_flips

                row.append(round_num_good_flips)
                row.append(round_num_too_early_flips)
                row.append(round_num_unneeded_flips)



                # Add in round numbers
                sid = int(row[1])
                if not sid in round_nums:
                    round_nums[sid] = 0
                row.append(round_nums[sid])
                round_nums[sid] += 1

            except ValueError:
                pass

        del row[6:10] #flips,blue score, red score, survey blob
        data.append(row)
        i = i+1

    print "Inserting data into output file."
    insertCsvData(data)

    print "Script finished."

def get_good_and_early(flips, tick, anchor):
    num_good = 0
    num_early = 0

    tick = int(tick)
    anchor=int(anchor)

    firstflipRecorded = []

    for flip in flips:
        flip = int(flip)
        front_diff = (flip-anchor)%tick
        back_diff = tick-front_diff
        period_num = (flip-anchor)/tick
        good = 0

        if back_diff < 50:
            num_early += 1

        if not period_num in firstflipRecorded:
            if(back_diff > 50): # the 50 here begins the 'DEATH ZONE!!!'
                num_good += 1

            firstflipRecorded.append(period_num)

    return {'good':num_good, 'early':num_early}


def get_json_data_from(row, newcolnames):
    blob = row[9]
    blob = json.loads(blob)
    #pprint(blob)
    result = []

    for cname in newcolnames:
        if(cname in blob):
            result.append(blob[cname])

    #pprint(result)
    return result


def get_player_flips(flipString):
    flipArr = flipString.split(',')
    result = []
    for flip in flipArr:
        posplayer = flip.split(':')
        pos = posplayer[0]
        player = posplayer[1]

        if(player == 'X'):
            result.append(pos)

    return result


def get_round_payment(row):

    bs = row[7]
    bs = json.loads(bs)[-1]

    rs = row[8]
    rs = json.loads(rs)[-1]

    delta = bs-rs
    delta = max(0, 1000+delta)

    exchange_rate = 0.00004  # that's $0.002 per 50 cookies
    bonus = delta * exchange_rate
    bonus = str(round(bonus, 2))+''

    #pprint(bonus)

    return bonus


def insertCsvData(data):
    outfh = open('outfile.csv', 'w')
    writer = csv.writer(outfh, dialect = 'excel')
    writer.writerows(data)

main()
