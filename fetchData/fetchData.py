#!/usr/bin/python3
import sys
import os
import os.path
import shutil
import pandas as pd
import numpy as np
import requests
import filecmp
import subprocess
from datetime import datetime

def download(url, file_name):
    with open(file_name, "wb") as file:
        response = requests.get(url)
        file.write(response.content)

    if response.status_code != 200:
        sys.exit('Downlaod Error: StatusCode '+str(response.status_code)+' URL:'+url)


def getDataFromInternet():
    downloads = {
        'amtliches-gebaeudeadressverzeichnis_ch_2056.csv': 'https://data.geo.admin.ch/ch.swisstopo.amtliches-gebaeudeadressverzeichnis/amtliches-gebaeudeadressverzeichnis_ch/',
        'ortschaftenverzeichnis_plz_2056.csv': 'https://data.geo.admin.ch/ch.swisstopo-vd.ortschaftenverzeichnis_plz/ortschaftenverzeichnis_plz/'
    }
    for file, url_path in downloads.items():
        file_name = file+'.zip'
        extract_folder = './'+file
        download(url=url_path+file_name, file_name=file_name)

        if os.path.exists(file_name):
            if os.path.isdir(extract_folder):
                shutil.rmtree(extract_folder, ignore_errors=True)
            shutil.unpack_archive(file_name, extract_folder)
            os.remove(file_name)
        else:
            sys.exit('File does not exist: '+file_name)

def getOrtschaftenverzeichnis():
    file_name = r'./ortschaftenverzeichnis_plz_2056.csv/AMTOVZ_CSV_LV95/AMTOVZ_CSV_LV95.csv'
    if os.path.exists(file_name):
        dfTown = pd.read_csv(file_name, sep=';', engine='python', dtype='unicode')
    else:
        sys.exit('File does not exist '+file_name)

    dfTown = dfTown.reset_index(drop=True)
    dfTown = dfTown.drop(['Zusatzziffer', 'E', 'N', 'Validity'], axis=1)
    dfTown = dfTown.rename(columns={'PLZ': 'zip', 'BFS-Nr': 'bfs', 'Gemeindename': 'municipality', 'Ortschaftsname': 'town', 'Kantonskürzel': 'canton', 'Sprache': 'locale'})

    dfTown['bfs'] = dfTown['bfs'].astype(int)
    dfTown['zip'] = dfTown['zip'].astype(int)
    dfTown = dfTown[dfTown['canton'].notna()]

    dfTown = sanitizeTownWithNumberAtEnd(dfTown)

    return dfTown

def getGebaeudeverzeichnis():
    file_name = r'./amtliches-gebaeudeadressverzeichnis_ch_2056.csv/amtliches-gebaeudeadressverzeichnis_ch_2056.csv'
    if os.path.exists(file_name):
        df = pd.read_csv(file_name, sep=';', engine='python', dtype='unicode')
    else:
        sys.exit('File does not exist '+file_name)
    df = df.reset_index(drop=True)
    df = df.rename(columns={'COM_FOSNR': 'bfs'})
    df[['houseZip','houseTown']] = df['ZIP_LABEL'].str.split(' ', n=1, expand=True)
    dfStreet = df[['houseZip', 'houseTown', 'bfs']].copy()

    dfStreet['bfs'] = dfStreet['bfs'].astype(int)
    dfStreet['houseZip'] = dfStreet['houseZip'].astype(int)
    dfStreet['houseTown'] = dfStreet['houseTown'].str.strip()

    dfStreet = sanitizeTownWithNumberAtEnd(dfStreet, 'houseTown')
    dfStreet = getZipShareFromGebaeudeverzeichnis(dfStreet)
    return dfStreet


def getZipShareFromGebaeudeverzeichnis(df):
    df['houseCount'] = df.groupby(['houseTown', 'houseZip', 'bfs'])['houseZip'].transform('count').fillna(0)
    df['zipCount'] = df.groupby(['houseZip'])['houseZip'].transform('count')

    df = df.groupby(['houseTown', 'houseZip', 'bfs']).agg({'houseCount': 'size', 'zipCount': 'first'}).reset_index()

    df['zip-share'] = df['houseCount'] / df['zipCount']
    df['zip-share'] = df['zip-share'].fillna(0)  # Replace NaN with 0
    df.loc[(df['houseCount'] == 0) | (df['zipCount'] == 0), 'zip-share'] = 0  # Set 0 when houseCount or zipCount is 0
    df['zip-share'] = (df['zip-share'] * 100).round(2)

    df = df.drop(columns=['houseCount', 'zipCount'])
    return df

def sanitizeTownWithNumberAtEnd(df, column='town'):
    #sanatize Villages with number at end
    #df = df[df['village'].str.contains('\d', regex= True)]
    villageCleanup = {
        "Lausanne 27": "Lausanne",
        "Lausanne 26": "Lausanne",
        "Lausanne 25": "Lausanne",
        "Laax GR 2": "Laax GR"
    }
    for old, new in villageCleanup.items():
        df[column] = df[column].replace([old], new)
    return df

def addSpecialCityZipsWithoutBuildings(df):
    speicalCityZips = pd.read_csv(r'./add-city-zips.csv', sep=';', engine='python', dtype='unicode')
    speicalCityZips['municipality'] = speicalCityZips['town']
    speicalCityZips['bfs'] = speicalCityZips['bfs'].astype(int)
    speicalCityZips['zip'] = speicalCityZips['zip'].astype(int)
    speicalCityZips['zip-share'] = speicalCityZips['zip-share'].astype(float)
    df = pd.concat([speicalCityZips.dropna(), df], axis=0 )
    return df

def generateLegacyZipJsonV4(df):
    """
        Aggregate 'zip-share' data by 'bfs', derive 'town' from 'municipality',
        and save as legacy JSON for SwissZIP version 4.
    """
    print(df)
    dfLegacy = df.groupby(['bfs', 'zip'], as_index=False).agg({'zip-share':'sum', 'municipality':'first', 'canton':'first', 'locale':'first'})
    dfLegacy.reset_index(drop=True, inplace=True)
    dfLegacy.rename(columns={'municipality': 'town'}, inplace=True)
    dfLegacy = dfLegacy.sort_values(by=['zip', 'zip-share', 'town'], ascending=[True, False, True])
    dfLegacy.to_json(r'./../swissZIP/v4/data/zip.json', orient='records', indent=4)

def generateLegacyZipJsonV4(df):
    """
        Aggregate 'zip-share' data by 'bfs', derive 'town' from 'municipality',
        and save as legacy JSON for SwissZIP version 4.
    """
    dfLegacy = df.groupby(['bfs', 'zip'], as_index=False).agg({'zip-share':'sum', 'municipality':'first', 'canton':'first', 'locale':'first'})
    dfLegacy.reset_index(drop=True, inplace=True)
    dfLegacy.rename(columns={'municipality': 'town'}, inplace=True)
    dfLegacy = dfLegacy.sort_values(by=['zip', 'zip-share', 'town'], ascending=[True, False, True])
    dfLegacy.to_json(r'./../swissZIP/v4/data/zip.json', orient='records', indent=4)

def generateLegacyZipJsonV5(df):
    df.to_json(r'./../swissZIP/v5/data/zip_by_town.json', orient='records', indent=4)

    dfMuni = df.groupby(['bfs', 'zip'], as_index=False).agg({'zip-share':'sum', 'municipality':'first', 'canton':'first', 'locale':'first'})
    dfMuni.reset_index(drop=True, inplace=True)
    dfMuni.rename(columns={'municipality': 'town'}, inplace=True)
    dfMuni = dfMuni.sort_values(by=['zip', 'zip-share', 'town'], ascending=[True, False, True])
    dfMuni.to_json(r'./../swissZIP/v5/data/zip_by_municipality.json', orient='records', indent=4)


def compileList(dfTown, dfHouse):
    df = dfTown.merge(dfHouse, how='right', left_on=['zip', 'bfs', 'town'], right_on=['houseZip', 'bfs', 'houseTown'])
    df.drop(columns='houseZip', inplace=True)
    df = addSpecialCityZipsWithoutBuildings(df)

    #group to integrate
    df = df.groupby(['bfs', 'zip', 'town'], as_index=False).agg({'zip-share':'max', 'municipality':'first', 'canton':'first', 'locale':'first'})
    df.reset_index(drop=True, inplace=True)
    df = df.sort_values(by=['zip', 'zip-share', 'town'], ascending=[True, False, True])

    df['municipality'] = df.apply(lambda row: row['town'] if pd.isna(row['municipality']) else row['municipality'], axis=1)

    df = df.dropna(subset=['canton']) #remove Lichtenstein

    df = df.loc[:, ['zip', 'zip-share', 'town', 'bfs', 'municipality', 'canton', 'locale']]
    df = df.astype({'zip':'int', 'zip-share':'float', 'town':'string', 'bfs':'int', 'municipality':'string', 'canton':'string', 'locale':'string'})
    df = df.sort_values(by=['zip', 'zip-share', 'town'], ascending=[True, False, True])
    return df

def cleanup():
    files = [   r'./PLZO_CSV_LV95',
                r'./amtliches-gebaeudeadressverzeichnis_ch_2056.csv',
    ]
    for f in files:
        if os.path.exists(f):
            if os.path.isdir(f):
                shutil.rmtree(f, ignore_errors=True)
            else:
                os.remove(f)

def main():
    getDataFromInternet()
    dfTown = getOrtschaftenverzeichnis()
    dfHouse = getGebaeudeverzeichnis()
    df = compileList(dfTown, dfHouse)
    generateLegacyZipJsonV4(df)
    generateLegacyZipJsonV5(df)
    cleanup()


if __name__ == "__main__":
    main()
