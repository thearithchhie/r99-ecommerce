<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { ChevronLeft, User, Mail, UserCheck, Clock, Building } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';

const route = useRoute();
const router = useRouter();
const userId = route.params.id;
const loading = ref(true);
const user = ref(null);
const notFound = ref(false);

// Dummy user data that matches UsersView data with additional details
const dummyUsers = [
  { id: 1, name: 'John Doe', email: 'john@example.com', role: 'Admin', status: 'Active', lastLogin: '2023-03-20 14:30', phone: '+1 (555) 123-4567', address: '123 Main St, New York, NY 10001', joinDate: '2022-01-15', department: 'IT Administration' },
  { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-19 09:15', phone: '+1 (555) 987-6543', address: '456 Oak Ave, San Francisco, CA 94102', joinDate: '2022-02-20', department: 'Sales' },
  { id: 3, name: 'Robert Johnson', email: 'robert@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-15 11:45', phone: '+1 (555) 246-8109', address: '789 Pine St, Chicago, IL 60601', joinDate: '2022-03-10', department: 'Marketing' },
  { id: 4, name: 'Emily Davis', email: 'emily@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-18 16:20', phone: '+1 (555) 369-2580', address: '321 Maple Rd, Boston, MA 02108', joinDate: '2022-01-05', department: 'Human Resources' },
  { id: 5, name: 'Michael Wilson', email: 'michael@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-17 13:10', phone: '+1 (555) 159-7532', address: '654 Elm St, Seattle, WA 98101', joinDate: '2022-02-28', department: 'Finance' },
  { id: 6, name: 'Sarah Thompson', email: 'sarah@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-16 10:30', phone: '+1 (555) 753-9514', address: '987 Cedar Ln, Austin, TX 78701', joinDate: '2022-01-25', department: 'Customer Support' },
  { id: 7, name: 'David Anderson', email: 'david@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-10 09:45', phone: '+1 (555) 852-7410', address: '741 Birch St, Denver, CO 80202', joinDate: '2022-03-05', department: 'Sales' },
  { id: 8, name: 'Jennifer Lee', email: 'jennifer@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-14 15:20', phone: '+1 (555) 963-8520', address: '369 Willow Ave, Miami, FL 33101', joinDate: '2022-02-15', department: 'Marketing' },
  { id: 9, name: 'William Martinez', email: 'william@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-13 11:35', phone: '+1 (555) 741-2580', address: '258 Ash St, Portland, OR 97201', joinDate: '2022-01-10', department: 'Human Resources' },
  { id: 10, name: 'Jessica Taylor', email: 'jessica@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-12 14:50', phone: '+1 (555) 147-9630', address: '147 Oakwood Dr, Nashville, TN 37203', joinDate: '2022-03-15', department: 'Finance' },
  { id: 11, name: 'Daniel Brown', email: 'daniel@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-08 09:15', phone: '+1 (555) 369-1470', address: '369 Maple Ave, Phoenix, AZ 85001', joinDate: '2022-02-25', department: 'Customer Support' },
  { id: 12, name: 'Amanda White', email: 'amanda@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-11 16:30', phone: '+1 (555) 258-7410', address: '258 Pine Ln, Philadelphia, PA 19102', joinDate: '2022-01-20', department: 'Marketing' },
  { id: 13, name: 'Matthew Miller', email: 'matthew@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-09 13:45', phone: '+1 (555) 852-9630', address: '852 Elm Ave, San Diego, CA 92101', joinDate: '2022-03-01', department: 'IT Administration' },
  { id: 14, name: 'Olivia Harris', email: 'olivia@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-07 10:20', phone: '+1 (555) 963-1470', address: '963 Cedar St, Dallas, TX 75201', joinDate: '2022-02-10', department: 'Sales' },
  { id: 15, name: 'Andrew Clark', email: 'andrew@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-05 15:10', phone: '+1 (555) 741-9630', address: '741 Oak St, Atlanta, GA 30303', joinDate: '2022-01-30', department: 'Finance' },
  { id: 16, name: 'Sophia Lewis', email: 'sophia@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-06 11:30', phone: '+1 (555) 147-8520', address: '147 Pine Ave, Las Vegas, NV 89101', joinDate: '2022-03-20', department: 'Marketing' },
  { id: 17, name: 'Joseph Young', email: 'joseph@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-04 09:45', phone: '+1 (555) 369-7410', address: '369 Birch Ln, New Orleans, LA 70112', joinDate: '2022-02-05', department: 'Customer Support' },
  { id: 18, name: 'Emma Walker', email: 'emma@example.com', role: 'Manager', status: 'Active', lastLogin: '2023-03-03 14:20', phone: '+1 (555) 258-9630', address: '258 Willow St, Seattle, WA 98101', joinDate: '2022-01-15', department: 'Human Resources' },
  { id: 19, name: 'Alexander Hall', email: 'alexander@example.com', role: 'Customer', status: 'Inactive', lastLogin: '2023-03-02 10:35', phone: '+1 (555) 852-1470', address: '852 Oakwood Ave, Chicago, IL 60601', joinDate: '2022-03-25', department: 'Finance' },
  { id: 20, name: 'Ava Allen', email: 'ava@example.com', role: 'Customer', status: 'Active', lastLogin: '2023-03-01 15:50', phone: '+1 (555) 963-7410', address: '963 Maple St, San Francisco, CA 94102', joinDate: '2022-02-01', department: 'Sales' }
];

onMounted(() => {
  // Simulate API call delay
  setTimeout(() => {
    const foundUser = dummyUsers.find(u => u.id === parseInt(userId as string));
    if (foundUser) {
      user.value = foundUser;
    } else {
      notFound.value = true;
    }
    loading.value = false;
  }, 500);
});

const getStatusClass = (status) => {
  return status === 'Active' 
    ? 'bg-green-100 text-green-800' 
    : 'bg-gray-100 text-gray-800';
};

const goBack = () => {
  router.push('/users');
};
</script>

<template>
  <div>
    <!-- Header with back button -->
    <div class="flex items-center mb-6">
      <Button variant="outline" class="mr-4" @click="goBack">
        <ChevronLeft class="h-4 w-4 mr-2" />
        Back to Users
      </Button>
      <h1 class="text-2xl font-bold">User Details</h1>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="bg-white rounded-lg shadow p-8 text-center">
      <div class="flex justify-center">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-primary"></div>
      </div>
      <p class="mt-4 text-gray-500">Loading user details...</p>
    </div>

    <!-- Not found state -->
    <div v-else-if="notFound" class="bg-white rounded-lg shadow p-8 text-center">
      <div class="text-red-500 mb-4 text-lg">User not found</div>
      <p class="mb-6 text-gray-500">The user you are looking for does not exist or has been removed.</p>
      <Button @click="goBack">Return to User List</Button>
    </div>

    <!-- User details -->
    <div v-else-if="user" class="bg-white rounded-lg shadow">
      <!-- User header -->
      <div class="p-6 border-b border-gray-200">
        <div class="flex items-center">
          <div class="h-16 w-16 rounded-full bg-gray-100 flex items-center justify-center text-gray-600 font-semibold text-xl mr-4">
            {{ user.name.charAt(0) }}
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">{{ user.name }}</h2>
            <div class="mt-1 flex items-center">
              <span :class="['px-2 py-1 text-xs font-medium rounded-full', getStatusClass(user.status)]">
                {{ user.status }}
              </span>
              <span class="ml-2 px-2 py-1 bg-gray-100 text-gray-800 text-xs font-medium rounded-full">
                {{ user.role }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- User information -->
      <div class="p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Contact Information -->
          <div class="space-y-4">
            <div class="flex items-start">
              <Mail class="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-500">Email</div>
                <div class="mt-1">{{ user.email }}</div>
              </div>
            </div>
            
            <div class="flex items-start">
              <User class="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-500">Phone</div>
                <div class="mt-1">{{ user.phone }}</div>
              </div>
            </div>
            
            <div class="flex items-start">
              <Building class="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-500">Address</div>
                <div class="mt-1">{{ user.address }}</div>
              </div>
            </div>
          </div>
          
          <!-- Account Information -->
          <div class="space-y-4">
            <div class="flex items-start">
              <UserCheck class="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-500">Department</div>
                <div class="mt-1">{{ user.department }}</div>
              </div>
            </div>
            
            <div class="flex items-start">
              <Clock class="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-500">Join Date</div>
                <div class="mt-1">{{ user.joinDate }}</div>
              </div>
            </div>
            
            <div class="flex items-start">
              <Clock class="h-5 w-5 text-gray-400 mt-0.5 mr-3" />
              <div>
                <div class="text-sm font-medium text-gray-500">Last Login</div>
                <div class="mt-1">{{ user.lastLogin }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="p-6 border-t border-gray-200">
        <div class="flex gap-3">
          <Button class="bg-blue-600 hover:bg-blue-700" @click="router.push(`/users/edit/${user.id}`)">
            Edit User
          </Button>
          <Button variant="outline" class="text-red-600 border-red-200 hover:bg-red-50 hover:text-red-800">
            Delete User
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>