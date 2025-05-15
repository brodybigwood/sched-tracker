'''
                        try:
                            roles_raw = day.find_element(By.XPATH, "./"+role_xpath).get_attribute('textContent').lower()
                            hours_raw = day.find_element(By.XPATH, "./"+hours_xpath).get_attribute('textContent').lower()

                            workday = []

                            splitshift = []

                            for aliases in shift_roles:
                                found_alias = False
                                for alias in aliases:
                                    alias = alias.lower()
                                    count = hours_raw.count(alias)
                                    if count != 0:
                                        nextIndex = 0
                                        for x in range(count):
                                            index = hours_raw.find(alias, nextIndex)
                                            nextIndex = hours_raw.find(alias, index + len(alias))
                                            if nextIndex == -1:
                                                split_str = hours_raw[index:]  # Slice from index to the end
                                            else:
                                                split_str = hours_raw[index:nextIndex]
                                            splitshift.append(split_str)
                                            print(split_str)

                                        found_alias = True
                                        break
                                if found_alias:
                                    break

                            print(f"LENGTH OF SHIFT ARRAY: '{len(splitshift)}'")

                            for split in splitshift:
                                startTime, endTime = extractTime(split)

                                role = extractRole(split)
                                
                                hours = {
                                    'start': startTime,
                                    'end': endTime
                                }
                                shift = {
                                    "Role": role,
                                    "Hours": hours
                                }

                                workday.append(shift)

                            shifts[i] = workday

                        except Exception as e:
                            pass
               '''
 
