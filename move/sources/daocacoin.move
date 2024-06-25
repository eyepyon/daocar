
module my_addr::MyCoin {
    use std::string;
    use aptos_framework::coin;
    use aptos_framework::account;

    struct MyCoin {}

    const E_NOT_INITIALIZED: u64 = 1;

    public fun initialize(account: &signer) {
        let (burn_cap, freeze_cap, mint_cap) = coin::initialize<MyCoin>(
            account,
            string::utf8(b"DaoCaCoin"),
            string::utf8(b"DCC"),
            6,
            true
        );

        // Store the caps in the resource account
        account::create_resource_account(account, b"my_coin_resource");
        let resource_signer_cap = account::create_signer_with_capability(&account::create_resource_account_cap(account, b"my_coin_resource"));

        coin::register_mint_cap(&resource_signer_cap, mint_cap);
        coin::register_burn_cap(&resource_signer_cap, burn_cap);
        coin::register_freeze_cap(&resource_signer_cap, freeze_cap);
    }

    public entry fun mint(account: &signer, amount: u64) acquires MintCapStore {
        let mint_cap = &borrow_global<MintCapStore>(@my_addr).mint_cap;
        let coins_minted = coin::mint<MyCoin>(amount, mint_cap);
        coin::deposit(signer::address_of(account), coins_minted);
    }
}

