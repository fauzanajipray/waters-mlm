Nilai Bonus : 20.000
Bonus Ada :
1. Bonus Penjualan (BP) - Transaksi Normal : Bonus didapatkan oleh member yang melakukan penjualan
2. Bonus Penjualan (GM) - Transaksi Normal : Bonus apabila member di bawahnya 1 menjual barang
3. Bonus Penjualan (OR) - Transaksi Normal : Bonus apabila member dibawahnya 2 menjual barang
4. Bonus Penjualan Overriding 2 (OR2) - Transaksi Normal : Bonus yang didapatkan oleh member upline diatas 3, bonus sesuai level upline 
5. Komisi Cabang (KC) Jual Langsung - Transaksi Normal : Bonus yang didapatkan cabang/stokist apabila menjual barang.
    - Jika yang menjual member yg mempunyai cabang, maka dapat bonus (4.195%) -> Ada di config 'bonus_cabang_percentage_for_product', Apabila menjual produk dari stock cabang sendiri maka tidak mendapatkan bonus lagi.
    - Jika yang menjual member yg mempunyai cabanng stokist maka dapat bonus (2.0975%) -> Ada di config 'bonus_stokist_percentage_for_product'.
6. Komisi Cabang (KC) - Transaksi Stock : Bonus yang di dapatkan cabang apabila menambahkan stock produk.
    - Jika yang menambahkan stock cabang maka dapat bonus (4.195%) -> Ada di config 'bonus_cabang_percentage_for_stock', mengisi field kc_type = 'STOCK'.
7. Komisi Stokist (KS) - Transaksi Stock : Bonus yang di dapatkan stokist apabila menambahkan stock produk.
    - Jika yang menambahkan stock stokist maka dapat bonus (2.0975%) -> sekarang di dapat dari komisi/2 di history transaksi stock cabang .
8. Komisi Sparepart (SS) - Transaksi Sparepart : Bonus penjualan sparepart didapatkan bila cabang/stokist/member menjulan sparepart.
    - jika member membeli langsung dari pusat maka dapt bonus 10%.
    - jika owner cabang/stokist membeli langsung dari pusat maka dapat bonus 20%.
    - jika owner stokist membeli sparepart dari cabang maka dapat bonus 15%, cabang harus memberikan bonusnya sebesar 15% dari total bonus awal ke stokist.
    - jika member membeli dari cabang maka dapat bonus 10%, cabang harus membarikan bonusnya sebesar 10% dari total bonus awal ke member.
    - jika member membeli dari stokist maka dapat bonus 10%, stokist harus memberikan bonusnya sebesar 10% dari total bonus awal ke member.
9. Komisi Sparepart (SS) - Transaksi Stock : Bonus penjualan sparepart didapatkan bila cabang/stokist menambahkan stock sparepart.
    - jika cabang menambahkan stock sparepart maka dapat bonus 20%,
    - jika stokist menambahkan stock sparepart maka dapat bonus 15%, bonus cabang yang tadinya 20% di update jadi 5%.
10. Komisi NSI (KN) - Transaksi Normal : Bonus yang didapatkan apabila terdapat member NSI. Komisi sesuai level NSI
    - Jika penjualan Normal dibantu NSI makan NSI dapat komisi, ditotal dihitung akhir bulan
    - Kalau NSI menjual barang makan dapat bonus BP dan komisi NSI
11. Komisi LSI : Bonus yang didapatkan apabila  
12. Komisi PM  