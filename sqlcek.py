import os
import requests
import time
from multiprocessing.dummy import Pool as ThreadPool
from colorama import Fore, Style

bl = Fore.BLUE
wh = Fore.WHITE
gr = Fore.GREEN
red = Fore.RED
res = Style.RESET_ALL
yl = Fore.YELLOW

headers = {'User-Agent': 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:77.0) Gecko/20100101 Firefox/77.0'}

def screen_clear():
    os.system('cls')

def lazycfg(star, config_file):
    if "://" not in star:
        star = "http://" + star
    star = star.replace('\n', '').replace('\r', '')
    url = star + config_file
    try:
        check = requests.get(url, headers=headers, timeout=3)
        if check.status_code == 200:
            resp = check.text
            if "pma_password" in resp or "auth[password]" in resp:
                print(f"sql {gr}OK{res} => {star}\n")
                with open("output/sqlpath.txt", "a") as f:
                    f.write(f'{url}\n')
            else:
                print(f"{red}Not Found{res} sql => {star}\n")
    except requests.exceptions.RequestException as e:
        print(f"{red}ERROR{res} {str(e)} => {star}\n")


def filter(star):
    lazycfg(star, "/adminer.php")
    lazycfg(star, "/phpmyadmin/index.php")
    lazycfg(star, "/myadmin/index.php")
    lazycfg(star, "/dbadmin/index.php")
    lazycfg(star, "/sql/index.php")
    lazycfg(star, "/phpadmin/index.php")
    lazycfg(star, "/mysql/index.php")
    lazycfg(star, "/pma/index.php")
    lazycfg(star, "/phpmyadmin2/index.php")
    lazycfg(star, "/sqlmanager/index.php")
    lazycfg(star, "/mysqlmanager/index.php")
    lazycfg(star, "/adminer/index.php")
def main():
    print(f'{gr}[ LAZYCONFIG  HUNTER ] | [ BY EsevenB ]')
    list_file = input(f"{gr}Give Me Your List.txt/{red}ENC0D3R> {gr}${res} ")
    with open(list_file, 'r') as f:
        star = f.readlines()
    try:
        with ThreadPool(100) as pool:
            pool.map(filter, star)
    except:
        pass

if __name__ == '__main__':
    screen_clear()
    main()
