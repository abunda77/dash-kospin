# Makan Bergizi Gratis - Fix Notes

## Issue Fixed: Foreign Key Constraint Error

### Problem
```
SQLSTATE[23000]: Integrity constraint violation: 1452 
Cannot add or update a child row: a foreign key constraint fails
```

### Root Cause
Tabel `profiles` menggunakan `id_user` sebagai primary key, bukan `id`. 
Controller awalnya menggunakan `$tabungan->id_profile` yang merujuk ke `profiles.id`, 
padahal foreign key constraint di migration menggunakan `profiles.id_user`.

### Solution Applied

#### 1. Migration Fix
**File**: `database/migrations/2025_10_09_174831_create_makan_bergizis_gratis_table.php`

**Before**:
```php
$table->foreignId('profile_id')->constrained('profiles', 'id_user')->onDelete('cascade');
```

**After**:
```php
$table->unsignedBigInteger('profile_id');
$table->foreign('profile_id')->references('id_user')->on('profiles')->onDelete('cascade');
```

#### 2. Controller Fix
**File**: `app/Http/Controllers/Api/MakanBergizisGratisController.php`

**Before**:
```php
'profile_id' => $tabungan->id_profile,
```

**After**:
```php
'profile_id' => $tabungan->profile->id_user, // Use id_user from profile
```

### Steps Taken

1. ✅ Rollback migration: `php artisan migrate:rollback --step=1`
2. ✅ Fix migration file (explicit foreign key definition)
3. ✅ Run migration: `php artisan migrate`
4. ✅ Fix controller to use `profile->id_user`
5. ✅ Clear test data: `MakanBergizisGratis::truncate()`
6. ✅ Test API endpoint - SUCCESS!

### Test Results

#### Test 1: Create Record (First Time)
```bash
POST /api/makan-bergizi-gratis
Body: {"no_tabungan":"8888-5592"}

Response: HTTP 201
{
  "success": true,
  "message": "Data Makan Bergizi Gratis berhasil dicatat",
  "data": {...}
}
```
✅ **PASSED**

#### Test 2: Duplicate Record (Same Day)
```bash
POST /api/makan-bergizi-gratis
Body: {"no_tabungan":"8888-5592"}

Response: HTTP 409 Conflict
```
✅ **PASSED**

#### Test 3: Database Verification
```sql
SELECT * FROM makan_bergizis_gratis WHERE id = 1;

Result:
- id: 1
- tabungan_id: 5
- profile_id: 33 (correct id_user)
- no_tabungan: "8888-5592"
- All JSON data saved correctly
```
✅ **PASSED**

### Database Structure Clarification

**Profiles Table**:
- Primary Key: `id_user` (not `id`)
- Has both `id` and `id_user` columns
- `id_user` is the actual primary key

**Tabungans Table**:
- `id_profile` references `profiles.id` (not `id_user`)
- Relation in Tabungan model: `belongsTo(Profile::class, 'id_profile', 'id')`

**MakanBergizisGratis Table**:
- `profile_id` references `profiles.id_user` (primary key)
- Must use `$tabungan->profile->id_user` to get correct value

### Verification Commands

```bash
# Check profile structure
php artisan tinker --execute="echo (new App\Models\Profile)->getKeyName();"
# Output: id_user

# Check data relationship
php artisan tinker --execute="
  $t = App\Models\Tabungan::with('profile')->first();
  echo 'id_profile: ' . $t->id_profile . PHP_EOL;
  echo 'profile->id: ' . $t->profile->id . PHP_EOL;
  echo 'profile->id_user: ' . $t->profile->id_user . PHP_EOL;
"
```

### Status

✅ **FIXED & TESTED**

All tests passing:
- ✅ Create record works
- ✅ Duplicate prevention works (409)
- ✅ Data saved correctly in database
- ✅ Foreign key constraint satisfied
- ✅ No more constraint violations

### Lessons Learned

1. Always check the actual primary key of related models
2. Don't assume `id` is always the primary key
3. Use explicit foreign key definitions when primary key is not `id`
4. Test with actual data to catch constraint violations early

---

**Fixed By**: Kiro AI Assistant  
**Date**: October 9, 2025  
**Status**: ✅ RESOLVED
